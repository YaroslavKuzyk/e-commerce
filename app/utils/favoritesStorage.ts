const STORAGE_KEY = "favorites";

/**
 * Get favorite product IDs from localStorage
 */
export const getFavoritesFromStorage = (): number[] => {
  if (typeof window === "undefined") return [];

  try {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (!stored) return [];

    const parsed = JSON.parse(stored);
    if (!Array.isArray(parsed)) return [];

    return parsed.filter((id): id is number => typeof id === "number");
  } catch {
    return [];
  }
};

/**
 * Save favorite product IDs to localStorage
 */
export const saveFavoritesToStorage = (ids: number[]): void => {
  if (typeof window === "undefined") return;

  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
  } catch {
    console.error("Failed to save favorites to localStorage");
  }
};

/**
 * Add variant ID to favorites in localStorage
 */
export const addFavoriteToStorage = (variantId: number): number[] => {
  const favorites = getFavoritesFromStorage();
  if (!favorites.includes(variantId)) {
    favorites.push(variantId);
    saveFavoritesToStorage(favorites);
  }
  return favorites;
};

/**
 * Remove variant ID from favorites in localStorage
 */
export const removeFavoriteFromStorage = (variantId: number): number[] => {
  const favorites = getFavoritesFromStorage();
  const filtered = favorites.filter((id) => id !== variantId);
  saveFavoritesToStorage(filtered);
  return filtered;
};

/**
 * Toggle variant ID in favorites in localStorage
 */
export const toggleFavoriteInStorage = (
  variantId: number
): { favorites: number[]; isFavorite: boolean } => {
  const favorites = getFavoritesFromStorage();
  const index = favorites.indexOf(variantId);

  if (index === -1) {
    favorites.push(variantId);
    saveFavoritesToStorage(favorites);
    return { favorites, isFavorite: true };
  } else {
    favorites.splice(index, 1);
    saveFavoritesToStorage(favorites);
    return { favorites, isFavorite: false };
  }
};

/**
 * Check if variant is in favorites in localStorage
 */
export const isFavoriteInStorage = (variantId: number): boolean => {
  const favorites = getFavoritesFromStorage();
  return favorites.includes(variantId);
};

/**
 * Clear all favorites from localStorage
 */
export const clearFavoritesStorage = (): void => {
  if (typeof window === "undefined") return;

  try {
    localStorage.removeItem(STORAGE_KEY);
  } catch {
    console.error("Failed to clear favorites from localStorage");
  }
};
