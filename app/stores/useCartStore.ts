import { defineStore } from "pinia";
import type { Product } from "~/models/product";
import {
  getCartFromStorage,
  saveCartToStorage,
  clearCartStorage,
  type CartItem as StorageCartItem,
} from "~/utils/cartStorage";

export interface CartItem {
  variantId: number;
  quantity: number;
  variant?: Product;
}

interface CartResponse {
  success: boolean;
  data: Array<{
    id: number;
    variant: Product;
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
  const cartItems = ref<Map<number, number>>(new Map()); // variantId => quantity
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
   * Check if variant is in cart
   */
  const isInCart = (variantId: number): boolean => {
    return cartItems.value.has(variantId);
  };

  /**
   * Get quantity of variant in cart
   */
  const getQuantity = (variantId: number): number => {
    return cartItems.value.get(variantId) || 0;
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
      stored.map((item) => [item.variantId, item.quantity])
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
   * Add variant to cart (sets absolute quantity, not incremental)
   */
  const add = async (variantId: number, quantity: number = 1) => {
    const currentQty = cartItems.value.get(variantId) || 0;

    // If variant already in cart, just update quantity
    if (currentQty > 0) {
      return updateQuantity(variantId, currentQty + quantity, true);
    }

    // Optimistic update for new variant
    cartItems.value.set(variantId, quantity);

    if (isAuthenticated.value) {
      try {
        await client(`/api/cart/${variantId}`, {
          method: "POST",
          body: { quantity },
        });
      } catch (error) {
        // Rollback on error
        cartItems.value.delete(variantId);
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
   * Update variant quantity in cart
   */
  const updateQuantity = async (variantId: number, quantity: number, showToast: boolean = false) => {
    const prevQty = cartItems.value.get(variantId) || 0;

    if (quantity <= 0) {
      return remove(variantId);
    }

    // Optimistic update
    cartItems.value.set(variantId, quantity);

    if (isAuthenticated.value) {
      try {
        await client(`/api/cart/${variantId}`, {
          method: "PUT",
          body: { quantity },
        });
      } catch (error) {
        // Rollback on error
        cartItems.value.set(variantId, prevQty);
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
   * Remove variant from cart
   */
  const remove = async (variantId: number) => {
    const prevQty = cartItems.value.get(variantId);

    // Optimistic update
    cartItems.value.delete(variantId);

    if (isAuthenticated.value) {
      try {
        await client(`/api/cart/${variantId}`, {
          method: "DELETE",
        });
      } catch (error) {
        // Rollback on error
        if (prevQty) {
          cartItems.value.set(variantId, prevQty);
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
    cartItems.value.forEach((quantity, variantId) => {
      items.push({ variantId, quantity });
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
              variant_id: item.variantId,
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
   * Get full cart with variants (for cart page)
   */
  const getCartWithProducts = async () => {
    if (!isAuthenticated.value) {
      // For guest users, return items without variant details
      const items: CartItem[] = [];
      cartItems.value.forEach((quantity, variantId) => {
        items.push({ variantId, quantity });
      });
      return { items, meta: null };
    }

    try {
      const response = await client<CartResponse>("/api/cart");
      return {
        items: response.data.map((item) => ({
          variantId: item.variant.id,
          quantity: item.quantity,
          variant: item.variant,
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
