const STORAGE_KEY = "comparison";

export interface ComparisonItem {
  variantId: number;
  categoryId: number;
}

/**
 * Get comparison items from localStorage.
 */
export const getComparisonFromStorage = (): ComparisonItem[] => {
  if (typeof window === "undefined") return [];

  try {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (!stored) return [];

    const parsed = JSON.parse(stored);
    if (!Array.isArray(parsed)) return [];

    return parsed.filter(
      (item): item is ComparisonItem =>
        typeof item === "object" &&
        typeof item.variantId === "number" &&
        typeof item.categoryId === "number"
    );
  } catch {
    return [];
  }
};

/**
 * Get comparison items grouped by category ID.
 */
export const getComparisonGrouped = (): Map<number, number[]> => {
  const items = getComparisonFromStorage();
  const grouped = new Map<number, number[]>();

  for (const item of items) {
    const existing = grouped.get(item.categoryId) || [];
    if (!existing.includes(item.variantId)) {
      existing.push(item.variantId);
    }
    grouped.set(item.categoryId, existing);
  }

  return grouped;
};

/**
 * Save comparison items to localStorage.
 */
export const saveComparisonToStorage = (items: ComparisonItem[]): void => {
  if (typeof window === "undefined") return;

  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
  } catch {
    console.error("Failed to save comparison to localStorage");
  }
};

/**
 * Add variant to comparison.
 */
export const addToComparisonStorage = (
  variantId: number,
  categoryId: number
): ComparisonItem[] => {
  const items = getComparisonFromStorage();
  const exists = items.some((item) => item.variantId === variantId);

  if (!exists) {
    items.push({ variantId, categoryId });
    saveComparisonToStorage(items);
  }

  return items;
};

/**
 * Remove variant from comparison.
 */
export const removeFromComparisonStorage = (
  variantId: number
): ComparisonItem[] => {
  const items = getComparisonFromStorage();
  const filtered = items.filter((item) => item.variantId !== variantId);
  saveComparisonToStorage(filtered);
  return filtered;
};

/**
 * Toggle variant in comparison.
 */
export const toggleComparisonStorage = (
  variantId: number,
  categoryId: number
): { items: ComparisonItem[]; isCompared: boolean } => {
  const items = getComparisonFromStorage();
  const index = items.findIndex((item) => item.variantId === variantId);

  if (index === -1) {
    items.push({ variantId, categoryId });
    saveComparisonToStorage(items);
    return { items, isCompared: true };
  } else {
    items.splice(index, 1);
    saveComparisonToStorage(items);
    return { items, isCompared: false };
  }
};

/**
 * Check if variant is in comparison.
 */
export const isInComparisonStorage = (variantId: number): boolean => {
  const items = getComparisonFromStorage();
  return items.some((item) => item.variantId === variantId);
};

/**
 * Clear all comparisons for a category.
 */
export const clearCategoryComparisonStorage = (
  categoryId: number
): ComparisonItem[] => {
  const items = getComparisonFromStorage();
  const filtered = items.filter((item) => item.categoryId !== categoryId);
  saveComparisonToStorage(filtered);
  return filtered;
};

/**
 * Clear all comparisons.
 */
export const clearComparisonStorage = (): void => {
  if (typeof window === "undefined") return;

  try {
    localStorage.removeItem(STORAGE_KEY);
  } catch {
    console.error("Failed to clear comparison from localStorage");
  }
};

/**
 * Get total count of variants in comparison.
 */
export const getComparisonTotalCount = (): number => {
  return getComparisonFromStorage().length;
};

/**
 * Get count of categories with comparisons.
 */
export const getComparisonCategoryCount = (): number => {
  return getComparisonGrouped().size;
};
