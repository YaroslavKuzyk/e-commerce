const STORAGE_KEY = "cart";

export interface CartItem {
  productId: number;
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
        typeof item.productId === "number" &&
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
 * Add product to cart in localStorage (or update quantity if exists)
 */
export const addToCartStorage = (
  productId: number,
  quantity: number = 1
): CartItem[] => {
  const cart = getCartFromStorage();
  const existingIndex = cart.findIndex((item) => item.productId === productId);

  if (existingIndex !== -1) {
    cart[existingIndex].quantity += quantity;
  } else {
    cart.push({ productId, quantity });
  }

  saveCartToStorage(cart);
  return cart;
};

/**
 * Update product quantity in cart in localStorage
 */
export const updateCartItemStorage = (
  productId: number,
  quantity: number
): CartItem[] => {
  const cart = getCartFromStorage();
  const existingIndex = cart.findIndex((item) => item.productId === productId);

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
 * Remove product from cart in localStorage
 */
export const removeFromCartStorage = (productId: number): CartItem[] => {
  const cart = getCartFromStorage();
  const filtered = cart.filter((item) => item.productId !== productId);
  saveCartToStorage(filtered);
  return filtered;
};

/**
 * Get product quantity in cart
 */
export const getCartItemQuantity = (productId: number): number => {
  const cart = getCartFromStorage();
  const item = cart.find((item) => item.productId === productId);
  return item?.quantity || 0;
};

/**
 * Check if product is in cart
 */
export const isInCartStorage = (productId: number): boolean => {
  const cart = getCartFromStorage();
  return cart.some((item) => item.productId === productId);
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
