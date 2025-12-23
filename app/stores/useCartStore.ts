import { defineStore } from "pinia";
import type { Product } from "~/models/product";
import {
  getCartFromStorage,
  saveCartToStorage,
  clearCartStorage,
  type CartItem as StorageCartItem,
} from "~/utils/cartStorage";

export interface CartItem {
  productId: number;
  quantity: number;
  product?: Product;
}

interface CartResponse {
  success: boolean;
  data: Array<{
    id: number;
    product: Product;
    quantity: number;
    created_at: string;
  }>;
  meta: {
    total_items: number;
    total_quantity: number;
    total_price: number;
  };
}

interface CartSummaryResponse {
  success: boolean;
  data: Record<string, number>;
  meta: {
    total_items: number;
    total_quantity: number;
  };
}

interface CartSyncResponse {
  success: boolean;
  message: string;
  data: Record<string, number>;
  meta: {
    total_items: number;
    total_quantity: number;
  };
}

export const useCartStore = defineStore("cart", () => {
  const { user } = useSanctumAuth();
  const client = useSanctumClient();
  const toast = useToast();
  const { t } = useI18n();

  // State
  const cartItems = ref<Map<number, number>>(new Map()); // productId => quantity
  const isLoading = ref(false);
  const isInitialized = ref(false);

  // Computed
  const count = computed(() => cartItems.value.size);
  const totalQuantity = computed(() => {
    let total = 0;
    cartItems.value.forEach((qty) => {
      total += qty;
    });
    return total;
  });
  const isAuthenticated = computed(() => !!user.value);

  /**
   * Check if product is in cart
   */
  const isInCart = (productId: number): boolean => {
    return cartItems.value.has(productId);
  };

  /**
   * Get quantity of product in cart
   */
  const getQuantity = (productId: number): number => {
    return cartItems.value.get(productId) || 0;
  };

  /**
   * Initialize cart from localStorage or server
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
   * Load cart from localStorage (for guest users)
   */
  const loadFromStorage = () => {
    const stored = getCartFromStorage();
    cartItems.value = new Map(
      stored.map((item) => [item.productId, item.quantity])
    );
  };

  /**
   * Load cart from server (for authenticated users)
   */
  const loadFromServer = async () => {
    try {
      isLoading.value = true;
      const response = await client<CartSummaryResponse>("/api/cart/summary");
      cartItems.value = new Map(
        Object.entries(response.data).map(([id, qty]) => [Number(id), qty])
      );
    } catch (error) {
      console.error("Failed to load cart from server:", error);
    } finally {
      isLoading.value = false;
    }
  };

  /**
   * Add product to cart
   */
  const add = async (productId: number, quantity: number = 1) => {
    const currentQty = cartItems.value.get(productId) || 0;
    const newQty = currentQty + quantity;

    // Optimistic update
    cartItems.value.set(productId, newQty);

    if (isAuthenticated.value) {
      try {
        // Send absolute quantity to server (server sets this value, not adds to existing)
        const response = await client<{ success: boolean; data: { quantity: number } }>(`/api/cart/${productId}`, {
          method: "POST",
          body: { quantity: newQty },
        });
        // Update with server's actual quantity to stay in sync
        cartItems.value.set(productId, response.data.quantity);
      } catch (error) {
        // Rollback on error
        if (currentQty > 0) {
          cartItems.value.set(productId, currentQty);
        } else {
          cartItems.value.delete(productId);
        }
        console.error("Failed to add to cart:", error);
        return;
      }
    } else {
      saveToStorage();
    }

    toast.add({
      title: t("cart.addedToast"),
      color: "success",
    });
  };

  /**
   * Update product quantity in cart
   */
  const updateQuantity = async (productId: number, quantity: number, showToast: boolean = false) => {
    const prevQty = cartItems.value.get(productId) || 0;

    if (quantity <= 0) {
      return remove(productId);
    }

    // Optimistic update
    cartItems.value.set(productId, quantity);

    if (isAuthenticated.value) {
      try {
        await client(`/api/cart/${productId}`, {
          method: "PUT",
          body: { quantity },
        });
      } catch (error) {
        // Rollback on error
        cartItems.value.set(productId, prevQty);
        console.error("Failed to update cart:", error);
        return;
      }
    } else {
      saveToStorage();
    }

    if (showToast) {
      toast.add({
        title: t("cart.addedToast"),
        color: "success",
      });
    }
  };

  /**
   * Remove product from cart
   */
  const remove = async (productId: number) => {
    const prevQty = cartItems.value.get(productId);

    // Optimistic update
    cartItems.value.delete(productId);

    if (isAuthenticated.value) {
      try {
        await client(`/api/cart/${productId}`, {
          method: "DELETE",
        });
      } catch (error) {
        // Rollback on error
        if (prevQty) {
          cartItems.value.set(productId, prevQty);
        }
        console.error("Failed to remove from cart:", error);
        return;
      }
    } else {
      saveToStorage();
    }

    toast.add({
      title: t("cart.removedToast"),
      color: "info",
    });
  };

  /**
   * Clear entire cart
   */
  const clearCart = async () => {
    const prevItems = new Map(cartItems.value);

    // Optimistic update
    cartItems.value.clear();

    if (isAuthenticated.value) {
      try {
        await client("/api/cart", {
          method: "DELETE",
        });
      } catch (error) {
        // Rollback on error
        cartItems.value = prevItems;
        console.error("Failed to clear cart:", error);
        return;
      }
    } else {
      clearCartStorage();
    }
  };

  /**
   * Save cart to localStorage
   */
  const saveToStorage = () => {
    const items: StorageCartItem[] = [];
    cartItems.value.forEach((quantity, productId) => {
      items.push({ productId, quantity });
    });
    saveCartToStorage(items);
  };

  /**
   * Sync localStorage cart with server (called after login)
   */
  const syncWithServer = async () => {
    if (!isAuthenticated.value) return;

    const localCart = getCartFromStorage();

    if (localCart.length > 0) {
      try {
        const response = await client<CartSyncResponse>("/api/cart/sync", {
          method: "POST",
          body: {
            items: localCart.map((item) => ({
              product_id: item.productId,
              quantity: item.quantity,
            })),
          },
        });

        // Update local state with merged cart
        cartItems.value = new Map(
          Object.entries(response.data).map(([id, qty]) => [Number(id), qty])
        );

        // Clear localStorage after sync
        clearCartStorage();
      } catch (error) {
        console.error("Failed to sync cart:", error);
      }
    } else {
      // No local cart, just load from server
      await loadFromServer();
    }
  };

  /**
   * Get full cart with products (for cart page)
   */
  const getCartWithProducts = async () => {
    if (!isAuthenticated.value) {
      // For guest users, return items without product details
      const items: CartItem[] = [];
      cartItems.value.forEach((quantity, productId) => {
        items.push({ productId, quantity });
      });
      return { items, meta: null };
    }

    try {
      const response = await client<CartResponse>("/api/cart");
      return {
        items: response.data.map((item) => ({
          productId: item.product.id,
          quantity: item.quantity,
          product: item.product,
        })),
        meta: response.meta,
      };
    } catch (error) {
      console.error("Failed to get cart with products:", error);
      return { items: [], meta: null };
    }
  };

  /**
   * Clear cart state (called on logout)
   */
  const clear = () => {
    cartItems.value = new Map();
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
    cartItems,
    isLoading,
    isInitialized,

    // Computed
    count,
    totalQuantity,
    isAuthenticated,

    // Methods
    isInCart,
    getQuantity,
    init,
    add,
    updateQuantity,
    remove,
    clearCart,
    syncWithServer,
    getCartWithProducts,
    clear,
    reset,
  };
});
