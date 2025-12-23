import { defineStore } from "pinia";
import type { Product } from "~/models/product";
import {
  getComparisonFromStorage,
  saveComparisonToStorage,
  clearComparisonStorage,
  clearComparisonByCategoryStorage,
  type ComparisonItem,
} from "~/utils/comparisonStorage";

export interface ComparisonCategory {
  category: {
    id: number;
    name: string;
    slug: string;
  };
  products_count: number;
  products: Product[];
}

interface ComparisonSummaryResponse {
  success: boolean;
  data: Record<string, number[]>;
  meta: {
    total_categories: number;
    total_products: number;
  };
}

interface ComparisonSyncResponse {
  success: boolean;
  message: string;
  data: Record<string, number[]>;
  meta: {
    total_categories: number;
    total_products: number;
  };
}

interface ComparisonListResponse {
  success: boolean;
  data: ComparisonCategory[];
  meta: {
    total_categories: number;
    total_products: number;
  };
}

export const useComparisonStore = defineStore("comparison", () => {
  const { user } = useSanctumAuth();
  const client = useSanctumClient();
  const toast = useToast();
  const { t } = useI18n();

  // State: productId => categoryId
  const comparisonItems = ref<Map<number, number>>(new Map());
  const isLoading = ref(false);
  const isInitialized = ref(false);

  // Computed
  const count = computed(() => comparisonItems.value.size);
  const isAuthenticated = computed(() => !!user.value);

  // Get count by category
  const countByCategory = computed(() => {
    const counts: Record<number, number> = {};
    comparisonItems.value.forEach((categoryId) => {
      counts[categoryId] = (counts[categoryId] || 0) + 1;
    });
    return counts;
  });

  // Get categories count
  const categoriesCount = computed(() => {
    const categories = new Set<number>();
    comparisonItems.value.forEach((categoryId) => {
      categories.add(categoryId);
    });
    return categories.size;
  });

  /**
   * Check if product is in comparison
   */
  const isInComparison = (productId: number): boolean => {
    return comparisonItems.value.has(productId);
  };

  /**
   * Get category ID for a product in comparison
   */
  const getCategoryId = (productId: number): number | undefined => {
    return comparisonItems.value.get(productId);
  };

  /**
   * Get product IDs by category
   */
  const getProductIdsByCategory = (categoryId: number): number[] => {
    const productIds: number[] = [];
    comparisonItems.value.forEach((catId, productId) => {
      if (catId === categoryId) {
        productIds.push(productId);
      }
    });
    return productIds;
  };

  /**
   * Initialize comparison from localStorage or server
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
   * Load comparison from localStorage (for guest users)
   */
  const loadFromStorage = () => {
    const stored = getComparisonFromStorage();
    comparisonItems.value = new Map(
      stored.map((item) => [item.productId, item.categoryId])
    );
  };

  /**
   * Load comparison from server (for authenticated users)
   */
  const loadFromServer = async () => {
    try {
      isLoading.value = true;
      const response = await client<ComparisonSummaryResponse>("/api/comparisons/summary");

      const newMap = new Map<number, number>();
      Object.entries(response.data).forEach(([categoryId, productIds]) => {
        productIds.forEach((productId) => {
          newMap.set(productId, Number(categoryId));
        });
      });
      comparisonItems.value = newMap;
    } catch (error) {
      console.error("Failed to load comparisons from server:", error);
    } finally {
      isLoading.value = false;
    }
  };

  /**
   * Add product to comparison
   */
  const add = async (productId: number, categoryId: number) => {
    if (comparisonItems.value.has(productId)) {
      return; // Already in comparison
    }

    // Optimistic update
    comparisonItems.value.set(productId, categoryId);

    if (isAuthenticated.value) {
      try {
        await client(`/api/comparisons/${productId}`, {
          method: "POST",
        });
      } catch (error) {
        // Rollback on error
        comparisonItems.value.delete(productId);
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
   * Remove product from comparison
   */
  const remove = async (productId: number) => {
    const prevCategoryId = comparisonItems.value.get(productId);

    if (prevCategoryId === undefined) {
      return; // Not in comparison
    }

    // Optimistic update
    comparisonItems.value.delete(productId);

    if (isAuthenticated.value) {
      try {
        await client(`/api/comparisons/${productId}`, {
          method: "DELETE",
        });
      } catch (error) {
        // Rollback on error
        comparisonItems.value.set(productId, prevCategoryId);
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
   * Toggle product in comparison
   */
  const toggle = async (productId: number, categoryId: number) => {
    if (comparisonItems.value.has(productId)) {
      await remove(productId);
    } else {
      await add(productId, categoryId);
    }
  };

  /**
   * Clear all comparisons or by category
   */
  const clearComparison = async (categoryId?: number) => {
    const prevItems = new Map(comparisonItems.value);

    // Optimistic update
    if (categoryId !== undefined) {
      comparisonItems.value.forEach((catId, productId) => {
        if (catId === categoryId) {
          comparisonItems.value.delete(productId);
        }
      });
    } else {
      comparisonItems.value.clear();
    }

    if (isAuthenticated.value) {
      try {
        const url = categoryId !== undefined
          ? `/api/comparisons?category_id=${categoryId}`
          : "/api/comparisons";
        await client(url, {
          method: "DELETE",
        });
      } catch (error) {
        // Rollback on error
        comparisonItems.value = prevItems;
        console.error("Failed to clear comparison:", error);
        return;
      }
    } else {
      if (categoryId !== undefined) {
        clearComparisonByCategoryStorage(categoryId);
      } else {
        clearComparisonStorage();
      }
    }
  };

  /**
   * Save comparison to localStorage
   */
  const saveToStorage = () => {
    const items: ComparisonItem[] = [];
    comparisonItems.value.forEach((categoryId, productId) => {
      items.push({ productId, categoryId });
    });
    saveComparisonToStorage(items);
  };

  /**
   * Sync localStorage comparison with server (called after login)
   */
  const syncWithServer = async () => {
    if (!isAuthenticated.value) return;

    const localComparison = getComparisonFromStorage();

    if (localComparison.length > 0) {
      try {
        const response = await client<ComparisonSyncResponse>("/api/comparisons/sync", {
          method: "POST",
          body: {
            items: localComparison.map((item) => ({
              product_id: item.productId,
            })),
          },
        });

        // Update local state with merged comparison
        const newMap = new Map<number, number>();
        Object.entries(response.data).forEach(([categoryId, productIds]) => {
          productIds.forEach((productId) => {
            newMap.set(productId, Number(categoryId));
          });
        });
        comparisonItems.value = newMap;

        // Clear localStorage after sync
        clearComparisonStorage();
      } catch (error) {
        console.error("Failed to sync comparison:", error);
      }
    } else {
      // No local comparison, just load from server
      await loadFromServer();
    }
  };

  /**
   * Get comparison lists with products (for comparison page)
   */
  const getComparisonLists = async (): Promise<ComparisonCategory[]> => {
    if (!isAuthenticated.value) {
      // For guest users, return empty - will need to load products by IDs
      return [];
    }

    try {
      const response = await client<ComparisonListResponse>("/api/comparisons");
      return response.data;
    } catch (error) {
      console.error("Failed to get comparison lists:", error);
      return [];
    }
  };

  /**
   * Clear comparison state (called on logout)
   */
  const clear = () => {
    comparisonItems.value = new Map();
    isInitialized.value = false;
  };

  /**
   * Reset and reload (useful when auth state changes)
   */
  const reset = () => {
    clear();
    init();
  };

  return {
    // State
    comparisonItems,
    isLoading,
    isInitialized,

    // Computed
    count,
    countByCategory,
    categoriesCount,
    isAuthenticated,

    // Methods
    isInComparison,
    getCategoryId,
    getProductIdsByCategory,
    init,
    add,
    remove,
    toggle,
    clearComparison,
    syncWithServer,
    getComparisonLists,
    clear,
    reset,
  };
});
