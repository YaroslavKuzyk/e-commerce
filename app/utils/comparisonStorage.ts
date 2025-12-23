/**
 * LocalStorage utilities for comparison (guest users)
 */

const STORAGE_KEY = 'comparison';

export interface ComparisonItem {
  productId: number;
  categoryId: number;
}

/**
 * Get comparison items from localStorage
 */
export const getComparisonFromStorage = (): ComparisonItem[] => {
  if (typeof window === 'undefined') return [];

  try {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (!stored) return [];
    return JSON.parse(stored);
  } catch {
    return [];
  }
};

/**
 * Save comparison items to localStorage
 */
export const saveComparisonToStorage = (items: ComparisonItem[]): void => {
  if (typeof window === 'undefined') return;

  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
  } catch (error) {
    console.error('Failed to save comparison to localStorage:', error);
  }
};

/**
 * Clear comparison from localStorage
 */
export const clearComparisonStorage = (): void => {
  if (typeof window === 'undefined') return;

  try {
    localStorage.removeItem(STORAGE_KEY);
  } catch (error) {
    console.error('Failed to clear comparison from localStorage:', error);
  }
};

/**
 * Add product to comparison in localStorage
 */
export const addToComparisonStorage = (productId: number, categoryId: number): void => {
  const items = getComparisonFromStorage();
  const exists = items.some(item => item.productId === productId);

  if (!exists) {
    items.push({ productId, categoryId });
    saveComparisonToStorage(items);
  }
};

/**
 * Remove product from comparison in localStorage
 */
export const removeFromComparisonStorage = (productId: number): void => {
  const items = getComparisonFromStorage();
  const filtered = items.filter(item => item.productId !== productId);
  saveComparisonToStorage(filtered);
};

/**
 * Check if product is in comparison
 */
export const isInComparisonStorage = (productId: number): boolean => {
  const items = getComparisonFromStorage();
  return items.some(item => item.productId === productId);
};

/**
 * Get product IDs grouped by category
 */
export const getComparisonByCategory = (): Record<number, number[]> => {
  const items = getComparisonFromStorage();
  const grouped: Record<number, number[]> = {};

  items.forEach(item => {
    if (!grouped[item.categoryId]) {
      grouped[item.categoryId] = [];
    }
    grouped[item.categoryId].push(item.productId);
  });

  return grouped;
};

/**
 * Clear comparison by category
 */
export const clearComparisonByCategoryStorage = (categoryId: number): void => {
  const items = getComparisonFromStorage();
  const filtered = items.filter(item => item.categoryId !== categoryId);
  saveComparisonToStorage(filtered);
};
