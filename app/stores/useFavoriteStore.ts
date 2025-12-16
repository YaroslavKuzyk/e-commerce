import { defineStore } from "pinia";
import type { Product } from "~/models/product";
import {
  getFavoritesFromStorage,
  saveFavoritesToStorage,
  clearFavoritesStorage,
} from "~/utils/favoritesStorage";

interface FavoritesResponse {
  success: boolean;
  data: Product[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

interface FavoriteIdsResponse {
  success: boolean;
  data: number[];
}

interface ToggleResponse {
  success: boolean;
  message: string;
  is_favorite: boolean;
}

interface SyncResponse {
  success: boolean;
  message: string;
  data: number[];
}

export const useFavoriteStore = defineStore("favorite", () => {
  const { user } = useSanctumAuth();
  const client = useSanctumClient();
  const toast = useToast();
  const { t } = useI18n();

  // State
  const favoriteIds = ref<Set<number>>(new Set());
  const isLoading = ref(false);
  const isInitialized = ref(false);

  // Computed
  const count = computed(() => favoriteIds.value.size);
  const isAuthenticated = computed(() => !!user.value);

  /**
   * Check if product is in favorites
   */
  const isFavorite = (productId: number): boolean => {
    return favoriteIds.value.has(productId);
  };

  /**
   * Initialize favorites from localStorage or server
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
   * Load favorites from localStorage (for guest users)
   */
  const loadFromStorage = () => {
    const stored = getFavoritesFromStorage();
    favoriteIds.value = new Set(stored);
  };

  /**
   * Load favorites from server (for authenticated users)
   */
  const loadFromServer = async () => {
    try {
      isLoading.value = true;
      const response = await client<FavoriteIdsResponse>("/api/favorites/ids");
      favoriteIds.value = new Set(response.data);
    } catch (error) {
      console.error("Failed to load favorites from server:", error);
    } finally {
      isLoading.value = false;
    }
  };

  /**
   * Add product to favorites
   */
  const add = async (productId: number) => {
    // Optimistic update
    favoriteIds.value.add(productId);

    if (isAuthenticated.value) {
      try {
        await client<ToggleResponse>(`/api/favorites/${productId}`, {
          method: "POST",
        });
      } catch (error) {
        // Rollback on error
        favoriteIds.value.delete(productId);
        console.error("Failed to add favorite:", error);
        return;
      }
    } else {
      saveFavoritesToStorage(Array.from(favoriteIds.value));
    }

    toast.add({
      title: t("favorites.addedToast"),
      color: "success",
    });
  };

  /**
   * Remove product from favorites
   */
  const remove = async (productId: number) => {
    // Optimistic update
    favoriteIds.value.delete(productId);

    if (isAuthenticated.value) {
      try {
        await client<ToggleResponse>(`/api/favorites/${productId}`, {
          method: "DELETE",
        });
      } catch (error) {
        // Rollback on error
        favoriteIds.value.add(productId);
        console.error("Failed to remove favorite:", error);
        return;
      }
    } else {
      saveFavoritesToStorage(Array.from(favoriteIds.value));
    }

    toast.add({
      title: t("favorites.removedToast"),
      color: "info",
    });
  };

  /**
   * Toggle product in favorites
   */
  const toggle = async (productId: number) => {
    if (isFavorite(productId)) {
      await remove(productId);
    } else {
      await add(productId);
    }
  };

  /**
   * Sync localStorage favorites with server (called after login)
   */
  const syncWithServer = async () => {
    if (!isAuthenticated.value) return;

    const localFavorites = getFavoritesFromStorage();

    if (localFavorites.length > 0) {
      try {
        const response = await client<SyncResponse>("/api/favorites/sync", {
          method: "POST",
          body: { product_ids: localFavorites },
        });

        // Update local state with merged favorites
        favoriteIds.value = new Set(response.data);

        // Clear localStorage after sync
        clearFavoritesStorage();
      } catch (error) {
        console.error("Failed to sync favorites:", error);
      }
    } else {
      // No local favorites, just load from server
      await loadFromServer();
    }
  };

  /**
   * Get paginated favorite products (for favorites page)
   */
  const getFavoriteProducts = async (page = 1, limit = 15) => {
    if (!isAuthenticated.value) {
      // For guest users, return empty since we only have IDs
      return { data: [], meta: null };
    }

    try {
      const response = await client<FavoritesResponse>(
        `/api/favorites?page=${page}&limit=${limit}`
      );
      return { data: response.data, meta: response.meta };
    } catch (error) {
      console.error("Failed to get favorite products:", error);
      return { data: [], meta: null };
    }
  };

  /**
   * Clear favorites (called on logout)
   */
  const clear = () => {
    favoriteIds.value = new Set();
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
    favoriteIds,
    isLoading,
    isInitialized,

    // Computed
    count,
    isAuthenticated,

    // Methods
    isFavorite,
    init,
    add,
    remove,
    toggle,
    syncWithServer,
    getFavoriteProducts,
    clear,
    reset,
  };
});
