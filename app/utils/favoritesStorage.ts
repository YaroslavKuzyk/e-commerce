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
 * Add product ID to favorites in localStorage
 */
export const addFavoriteToStorage = (productId: number): number[] => {
  const favorites = getFavoritesFromStorage();
  if (!favorites.includes(productId)) {
    favorites.push(productId);
    saveFavoritesToStorage(favorites);
  }
  return favorites;
};

/**
 * Remove product ID from favorites in localStorage
 */
export const removeFavoriteFromStorage = (productId: number): number[] => {
  const favorites = getFavoritesFromStorage();
  const filtered = favorites.filter((id) => id !== productId);
  saveFavoritesToStorage(filtered);
  return filtered;
};

/**
 * Toggle product ID in favorites in localStorage
 */
export const toggleFavoriteInStorage = (
  productId: number
): { favorites: number[]; isFavorite: boolean } => {
  const favorites = getFavoritesFromStorage();
  const index = favorites.indexOf(productId);

  if (index === -1) {
    favorites.push(productId);
    saveFavoritesToStorage(favorites);
    return { favorites, isFavorite: true };
  } else {
    favorites.splice(index, 1);
    saveFavoritesToStorage(favorites);
    return { favorites, isFavorite: false };
  }
};

/**
 * Check if product is in favorites in localStorage
 */
export const isFavoriteInStorage = (productId: number): boolean => {
  const favorites = getFavoritesFromStorage();
  return favorites.includes(productId);
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
