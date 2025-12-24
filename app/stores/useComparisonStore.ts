import { defineStore } from "pinia";
import type { Product } from "~/models/product";
import {
  getComparisonFromStorage,
  saveComparisonToStorage,
  clearComparisonStorage,
  type ComparisonItem,
} from "~/utils/comparisonStorage";

interface ComparisonCategory {
  category: {
    id: number;
    name: string;
    slug: string;
    logo_file_id?: number | null;
  };
  variants_count: number;
  variant_ids: number[];
  preview_images: number[];
}

interface ComparisonIdsResponse {
  success: boolean;
  data: Record<string, number[]>;
}

interface ComparisonListsResponse {
  success: boolean;
  data: ComparisonCategory[];
}

interface ComparisonCategoryResponse {
  success: boolean;
  data: {
    category: { id: number; name: string; slug: string };
    variants: Product[];
  };
}

interface ToggleResponse {
  success: boolean;
  message: string;
  is_compared: boolean;
  category_id: number;
}

interface SyncResponse {
  success: boolean;
  message: string;
  data: Record<string, number[]>;
}

export const useComparisonStore = defineStore("comparison", () => {
  const { user } = useSanctumAuth();
  const client = useSanctumClient();
  const toast = useToast();
  const { t } = useI18n();

  // State: Map<categoryId, Set<variantId>>
  const comparisonMap = ref<Map<number, Set<number>>>(new Map());
  const isLoading = ref(false);
  const isInitialized = ref(false);

  // Computed
  const totalCount = computed(() => {
    if (!comparisonMap.value) return 0;
    let total = 0;
    comparisonMap.value.forEach((variants) => {
      total += variants.size;
    });
    return total;
  });

  const categoryCount = computed(() => comparisonMap.value?.size ?? 0);

  const isAuthenticated = computed(() => !!user.value);

  /**
   * Check if variant is in comparison.
   */
  const isCompared = (variantId: number): boolean => {
    if (!comparisonMap.value) return false;
    for (const variants of comparisonMap.value.values()) {
      if (variants.has(variantId)) return true;
    }
    return false;
  };

  /**
   * Get variant IDs for a category.
   */
  const getVariantIdsByCategory = (categoryId: number): number[] => {
    if (!comparisonMap.value) return [];
    const variants = comparisonMap.value.get(categoryId);
    return variants ? Array.from(variants) : [];
  };

  /**
   * Initialize comparisons from localStorage or server.
   */
  const init = async () => {
    if (isInitialized.value) return;

    if (isAuthenticated.value) {
      await loadFromServer();
    } else {
      loadFromStorage();
    }

    isInitialized.value = true;
  };

  /**
   * Load comparisons from localStorage.
   */
  const loadFromStorage = () => {
    const stored = getComparisonFromStorage();
    const map = new Map<number, Set<number>>();

    for (const item of stored) {
      const existing = map.get(item.categoryId) || new Set();
      existing.add(item.variantId);
      map.set(item.categoryId, existing);
    }

    comparisonMap.value = map;
  };

  /**
   * Load comparisons from server.
   */
  const loadFromServer = async () => {
    try {
      isLoading.value = true;
      const response = await client<ComparisonIdsResponse>("/api/comparisons/ids");

      const map = new Map<number, Set<number>>();
      for (const [categoryId, variantIds] of Object.entries(response.data)) {
        map.set(Number(categoryId), new Set(variantIds));
      }

      comparisonMap.value = map;
    } catch (error) {
      console.error("Failed to load comparisons from server:", error);
    } finally {
      isLoading.value = false;
    }
  };

  /**
   * Add variant to comparison.
   */
  const add = async (variantId: number, categoryId: number) => {
    // Optimistic update
    const existing = comparisonMap.value.get(categoryId) || new Set();
    existing.add(variantId);
    comparisonMap.value.set(categoryId, existing);

    if (isAuthenticated.value) {
      try {
        await client<ToggleResponse>(`/api/comparisons/${variantId}`, {
          method: "POST",
        });
      } catch (error) {
        // Rollback on error
        existing.delete(variantId);
        if (existing.size === 0) {
          comparisonMap.value.delete(categoryId);
        }
        console.error("Failed to add to comparison:", error);
        return;
      }
    } else {
      saveToStorage();
    }

    toast.add({
      title: t("comparison.addedToast"),
      color: "success",
    });
  };

  /**
   * Remove variant from comparison.
   */
  const remove = async (variantId: number) => {
    // Find which category this variant belongs to
    let foundCategoryId: number | null = null;
    for (const [categoryId, variants] of comparisonMap.value.entries()) {
      if (variants.has(variantId)) {
        foundCategoryId = categoryId;
        break;
      }
    }

    if (foundCategoryId === null) return;

    // Optimistic update
    const existing = comparisonMap.value.get(foundCategoryId)!;
    existing.delete(variantId);
    if (existing.size === 0) {
      comparisonMap.value.delete(foundCategoryId);
    }

    if (isAuthenticated.value) {
      try {
        await client<ToggleResponse>(`/api/comparisons/${variantId}`, {
          method: "DELETE",
        });
      } catch (error) {
        // Rollback on error
        const existingSet =
          comparisonMap.value.get(foundCategoryId) || new Set();
        existingSet.add(variantId);
        comparisonMap.value.set(foundCategoryId, existingSet);
        console.error("Failed to remove from comparison:", error);
        return;
      }
    } else {
      saveToStorage();
    }

    toast.add({
      title: t("comparison.removedToast"),
      color: "info",
    });
  };

  /**
   * Toggle variant in comparison.
   */
  const toggle = async (variantId: number, categoryId: number) => {
    if (isCompared(variantId)) {
      await remove(variantId);
    } else {
      await add(variantId, categoryId);
    }
  };

  /**
   * Clear comparison for a category.
   */
  const clearCategory = async (categorySlug: string, categoryId: number) => {
    const prevVariants = comparisonMap.value.get(categoryId);

    // Optimistic update
    comparisonMap.value.delete(categoryId);

    if (isAuthenticated.value) {
      try {
        await client(`/api/comparisons/category/${categorySlug}`, {
          method: "DELETE",
        });
      } catch (error) {
        // Rollback on error
        if (prevVariants) {
          comparisonMap.value.set(categoryId, prevVariants);
        }
        console.error("Failed to clear category comparison:", error);
        return;
      }
    } else {
      saveToStorage();
    }
  };

  /**
   * Save current state to localStorage.
   */
  const saveToStorage = () => {
    const items: ComparisonItem[] = [];
    comparisonMap.value.forEach((variants, categoryId) => {
      variants.forEach((variantId) => {
        items.push({ variantId, categoryId });
      });
    });
    saveComparisonToStorage(items);
  };

  /**
   * Sync localStorage with server.
   */
  const syncWithServer = async () => {
    if (!isAuthenticated.value) return;

    const localItems = getComparisonFromStorage();

    if (localItems.length > 0) {
      try {
        const response = await client<SyncResponse>("/api/comparisons/sync", {
          method: "POST",
          body: {
            items: localItems.map((item) => ({
              variant_id: item.variantId,
              category_id: item.categoryId,
            })),
          },
        });

        // Update local state
        const map = new Map<number, Set<number>>();
        for (const [categoryId, variantIds] of Object.entries(response.data)) {
          map.set(Number(categoryId), new Set(variantIds));
        }
        comparisonMap.value = map;

        // Clear localStorage after sync
        clearComparisonStorage();
      } catch (error) {
        console.error("Failed to sync comparisons:", error);
      }
    } else {
      await loadFromServer();
    }
  };

  /**
   * Get comparison lists grouped by category (for overview page).
   */
  const getComparisonLists = async (): Promise<ComparisonCategory[]> => {
    if (!isAuthenticated.value) {
      // For guest, we can't get full category info without additional API call
      // Return empty and let the page handle guest scenario
      return [];
    }

    try {
      const response = await client<ComparisonListsResponse>("/api/comparisons");
      return response.data;
    } catch (error) {
      console.error("Failed to get comparison lists:", error);
      return [];
    }
  };

  /**
   * Get comparison variants for a category.
   */
  const getCategoryProducts = async (categorySlug: string) => {
    if (!isAuthenticated.value) {
      return null;
    }

    try {
      const response = await client<ComparisonCategoryResponse>(
        `/api/comparisons/category/${categorySlug}`
      );
      return response.data;
    } catch (error) {
      console.error("Failed to get category comparison:", error);
      return null;
    }
  };

  /**
   * Clear all comparisons.
   */
  const clear = () => {
    comparisonMap.value = new Map();
    isInitialized.value = false;
  };

  /**
   * Reset and reload.
   */
  const reset = () => {
    clear();
    init();
  };

  return {
    // State
    comparisonMap,
    isLoading,
    isInitialized,

    // Computed
    totalCount,
    categoryCount,
    isAuthenticated,

    // Methods
    isCompared,
    getVariantIdsByCategory,
    init,
    add,
    remove,
    toggle,
    clearCategory,
    syncWithServer,
    getComparisonLists,
    getCategoryProducts,
    clear,
    reset,
  };
});
