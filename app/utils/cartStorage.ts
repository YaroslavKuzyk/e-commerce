const STORAGE_KEY = "cart";

export interface CartItem {
  variantId: number;
  quantity: number;
}

/**
 * Get cart items from localStorage
 */
export const getCartFromStorage = (): CartItem[] => {
  if (typeof window === "undefined") return [];

  try {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (!stored) return [];

    const parsed = JSON.parse(stored);
    if (!Array.isArray(parsed)) return [];

    return parsed.filter(
      (item): item is CartItem =>
        typeof item === "object" &&
        typeof item.variantId === "number" &&
        typeof item.quantity === "number"
    );
  } catch {
    return [];
  }
};

/**
 * Save cart items to localStorage
 */
export const saveCartToStorage = (items: CartItem[]): void => {
  if (typeof window === "undefined") return;

  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
  } catch {
    console.error("Failed to save cart to localStorage");
  }
};

/**
 * Add variant to cart in localStorage (or update quantity if exists)
 */
export const addToCartStorage = (
  variantId: number,
  quantity: number = 1
): CartItem[] => {
  const cart = getCartFromStorage();
  const existingIndex = cart.findIndex((item) => item.variantId === variantId);

  if (existingIndex !== -1) {
    cart[existingIndex].quantity += quantity;
  } else {
    cart.push({ variantId, quantity });
  }

  saveCartToStorage(cart);
  return cart;
};

/**
 * Update variant quantity in cart in localStorage
 */
export const updateCartItemStorage = (
  variantId: number,
  quantity: number
): CartItem[] => {
  const cart = getCartFromStorage();
  const existingIndex = cart.findIndex((item) => item.variantId === variantId);

  if (existingIndex !== -1) {
    if (quantity <= 0) {
      cart.splice(existingIndex, 1);
    } else {
      cart[existingIndex].quantity = quantity;
    }
  }

  saveCartToStorage(cart);
  return cart;
};

/**
 * Remove variant from cart in localStorage
 */
export const removeFromCartStorage = (variantId: number): CartItem[] => {
  const cart = getCartFromStorage();
  const filtered = cart.filter((item) => item.variantId !== variantId);
  saveCartToStorage(filtered);
  return filtered;
};

/**
 * Get variant quantity in cart
 */
export const getCartItemQuantity = (variantId: number): number => {
  const cart = getCartFromStorage();
  const item = cart.find((item) => item.variantId === variantId);
  return item?.quantity || 0;
};

/**
 * Check if variant is in cart
 */
export const isInCartStorage = (variantId: number): boolean => {
  const cart = getCartFromStorage();
  return cart.some((item) => item.variantId === variantId);
};

/**
 * Get total items count in cart
 */
export const getCartTotalItems = (): number => {
  const cart = getCartFromStorage();
  return cart.length;
};

/**
 * Get total quantity in cart
 */
export const getCartTotalQuantity = (): number => {
  const cart = getCartFromStorage();
  return cart.reduce((sum, item) => sum + item.quantity, 0);
};

/**
 * Clear cart in localStorage
 */
export const clearCartStorage = (): void => {
  if (typeof window === "undefined") return;

  try {
    localStorage.removeItem(STORAGE_KEY);
  } catch {
    console.error("Failed to clear cart from localStorage");
  }
};
