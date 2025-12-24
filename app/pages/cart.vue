<template>
  <UContainer class="py-8">
    <!-- Breadcrumbs -->
    <div class="mb-6">
      <nav class="flex items-center gap-2 text-sm text-gray-500">
        <NuxtLink to="/" class="hover:text-primary">{{ $t("common.home") }}</NuxtLink>
        <ChevronRight class="w-4 h-4" />
        <span class="text-gray-900 dark:text-white">{{ $t("cart.title") }}</span>
      </nav>
    </div>

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center gap-3">
        <div class="w-1 h-8 bg-primary rounded-full"></div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ $t("cart.title") }}
        </h1>
        <span v-if="cartStore.count > 0" class="text-gray-500">
          ({{ cartStore.count }} {{ $t("cart.items", cartStore.count) }})
        </span>
      </div>
      <UButton
        v-if="cartStore.count > 0"
        variant="ghost"
        color="error"
        size="sm"
        @click="handleClearCart"
      >
        <template #leading>
          <Trash2 class="w-4 h-4" />
        </template>
        {{ $t("cart.clearCart") }}
      </UButton>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center py-16">
      <div class="flex flex-col items-center gap-3">
        <UButton loading variant="ghost" size="xl" />
        <span class="text-sm text-gray-500">{{ $t("cart.loading") }}</span>
      </div>
    </div>

    <!-- Guest State Notice -->
    <div v-else-if="!isAuthenticated && cartStore.count > 0" class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
      <p class="text-sm text-blue-700 dark:text-blue-300">
        {{ $t("cart.guestNotice") }}
      </p>
    </div>

    <!-- Cart Items -->
    <template v-if="!isLoading && cartStore.count > 0">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Products List -->
        <div class="lg:col-span-2">
          <div class="space-y-4">
            <div
              v-for="item in cartItems"
              :key="item.variantId"
              class="flex gap-4 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700"
            >
              <!-- Product Image -->
              <NuxtLink :to="item.variant ? buildProductUrl(item.variant) : '#'" class="shrink-0">
                <VSecureImage
                  v-if="item.variant?.main_image_file_id"
                  :file-id="item.variant.main_image_file_id"
                  img-class="w-24 h-24 object-contain rounded"
                />
                <div v-else class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center">
                  <Package class="w-8 h-8 text-gray-400" />
                </div>
              </NuxtLink>

              <!-- Product Info -->
              <div class="flex-1 min-w-0">
                <NuxtLink
                  :to="item.variant ? buildProductUrl(item.variant) : '#'"
                  class="font-medium text-gray-900 dark:text-white hover:text-primary line-clamp-2"
                >
                  {{ item.variant?.name || `Product #${item.variantId}` }}
                </NuxtLink>

                <!-- Quantity Controls -->
                <div class="flex items-center gap-3 mt-3">
                  <div class="flex items-center border border-gray-300 dark:border-gray-600 rounded-lg">
                    <button
                      class="px-3 py-1 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-l-lg transition-colors"
                      @click="decreaseQuantity(item.variantId, item.quantity)"
                    >
                      <Minus class="w-4 h-4" />
                    </button>
                    <span class="px-4 py-1 text-center min-w-[3rem] font-medium">
                      {{ item.quantity }}
                    </span>
                    <button
                      class="px-3 py-1 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-r-lg transition-colors"
                      @click="increaseQuantity(item.variantId, item.quantity)"
                    >
                      <Plus class="w-4 h-4" />
                    </button>
                  </div>
                  <button
                    class="p-2 text-gray-400 hover:text-red-500 transition-colors"
                    @click="removeItem(item.variantId)"
                  >
                    <Trash2 class="w-5 h-5" />
                  </button>
                </div>
              </div>

              <!-- Price -->
              <div class="text-right shrink-0">
                <div v-if="item.variant" class="font-bold text-lg text-gray-900 dark:text-white">
                  {{ formatPrice(getItemPrice(item.variant) * item.quantity) }}
                </div>
                <div
                  v-if="item.variant?.discount_price"
                  class="text-sm text-gray-500 line-through"
                >
                  {{ formatPrice(Number(item.variant.base_price) * item.quantity) }}
                </div>
                <div v-if="item.variant" class="text-sm text-gray-500 mt-1">
                  {{ formatPrice(getItemPrice(item.variant)) }} / {{ $t("cart.perItem") }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
          <div class="sticky top-4 p-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold mb-4">{{ $t("cart.orderSummary") }}</h2>

            <div class="space-y-3 mb-6">
              <div class="flex justify-between text-gray-600 dark:text-gray-400">
                <span>{{ $t("cart.subtotal") }} ({{ cartStore.totalQuantity }} {{ $t("cart.items", cartStore.totalQuantity) }})</span>
                <span>{{ formatPrice(totalPrice) }}</span>
              </div>
              <div v-if="totalDiscount > 0" class="flex justify-between text-green-600">
                <span>{{ $t("cart.discount") }}</span>
                <span>-{{ formatPrice(totalDiscount) }}</span>
              </div>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mb-6">
              <div class="flex justify-between text-lg font-bold">
                <span>{{ $t("cart.total") }}</span>
                <span class="text-primary">{{ formatPrice(totalPrice) }}</span>
              </div>
            </div>

            <UButton block size="lg" class="mb-3">
              {{ $t("cart.checkout") }}
            </UButton>
            <NuxtLink :to="localePath('/store')">
              <UButton block variant="outline" size="lg">
                {{ $t("cart.continueShopping") }}
              </UButton>
            </NuxtLink>
          </div>
        </div>
      </div>
    </template>

    <!-- Empty State -->
    <div v-else-if="!isLoading" class="text-center py-16">
      <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
        <ShoppingCart class="w-12 h-12 text-gray-400" />
      </div>
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
        {{ $t("cart.emptyTitle") }}
      </h2>
      <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
        {{ $t("cart.emptyDescription") }}
      </p>
      <NuxtLink :to="localePath('/store')">
        <UButton size="lg">
          <template #leading>
            <ShoppingBag class="w-5 h-5" />
          </template>
          {{ $t("cart.goToStore") }}
        </UButton>
      </NuxtLink>
    </div>
  </UContainer>
</template>

<script lang="ts" setup>
import {
  ShoppingCart,
  ChevronRight,
  ShoppingBag,
  Trash2,
  Plus,
  Minus,
  Package,
} from "lucide-vue-next";
import { useCartStore, type CartItem } from "~/stores/useCartStore";
import type { Product } from "~/models/product";
import VSecureImage from "~/components/common/VSecureImage.vue";
import { buildProductUrl as buildProductUrlUtil } from "~/utils/urlBuilder";

const { t } = useI18n();

// Build product URL from variant object
const buildProductUrl = (variant: Product): string => {
  const categoryPath: string[] = [];
  let currentCategory = variant.category;

  while (currentCategory) {
    categoryPath.unshift(currentCategory.slug);
    currentCategory = currentCategory.parent;
  }

  // Use variant_slug if available
  const variantSlug = (variant as any).variant_slug;
  return buildProductUrlUtil(categoryPath, variant.slug, variantSlug);
};
const localePath = useLocalePath();
const cartStore = useCartStore();
const client = useSanctumClient();
const { user } = useSanctumAuth();

const isAuthenticated = computed(() => !!user.value);
const isLoading = ref(true);
const cartItems = ref<CartItem[]>([]);

// Get item price (current_price or base_price)
const getItemPrice = (variant: Product): number => {
  if (variant.current_price !== undefined) {
    return variant.current_price;
  }
  if (variant.discount_price) {
    return Number(variant.discount_price);
  }
  return Number(variant.base_price);
};

// Calculate prices
const totalPrice = computed(() => {
  return cartItems.value.reduce((sum, item) => {
    if (!item.variant) return sum;
    return sum + getItemPrice(item.variant) * item.quantity;
  }, 0);
});

const totalDiscount = computed(() => {
  return cartItems.value.reduce((sum, item) => {
    if (!item.variant || !item.variant.discount_price) return sum;
    const basePrice = Number(item.variant.base_price);
    const discountPrice = Number(item.variant.discount_price);
    return sum + (basePrice - discountPrice) * item.quantity;
  }, 0);
});

// Format price
const formatPrice = (price: number) => {
  return new Intl.NumberFormat("uk-UA", {
    style: "currency",
    currency: "UAH",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(price);
};

// Initialize cart
onMounted(async () => {
  await cartStore.init();
  await loadCartItems();
  isLoading.value = false;
});

// Load cart items with variants
const loadCartItems = async () => {
  if (isAuthenticated.value) {
    const result = await cartStore.getCartWithProducts();
    cartItems.value = result.items;
  } else {
    // For guest users, load variants by IDs
    const items: CartItem[] = [];
    cartStore.cartItems.forEach((quantity, variantId) => {
      items.push({ variantId, quantity });
    });

    if (items.length > 0) {
      try {
        const ids = items.map((i) => i.variantId).join(",");
        const response = await client<{ success: boolean; data: Product[] }>(
          `/api/products/variants?ids=${ids}&limit=50`
        );
        const variantsMap = new Map(response.data.map((v: any) => [v.variant_id || v.id, v]));

        cartItems.value = items.map((item) => ({
          ...item,
          variant: variantsMap.get(item.variantId),
        }));
      } catch (error) {
        console.error("Failed to load cart variants:", error);
        cartItems.value = items;
      }
    } else {
      cartItems.value = [];
    }
  }
};

// Quantity controls
const increaseQuantity = async (variantId: number, currentQty: number) => {
  await cartStore.updateQuantity(variantId, currentQty + 1);
  const item = cartItems.value.find((i) => i.variantId === variantId);
  if (item) {
    item.quantity = currentQty + 1;
  }
};

const decreaseQuantity = async (variantId: number, currentQty: number) => {
  if (currentQty <= 1) {
    await removeItem(variantId);
  } else {
    await cartStore.updateQuantity(variantId, currentQty - 1);
    const item = cartItems.value.find((i) => i.variantId === variantId);
    if (item) {
      item.quantity = currentQty - 1;
    }
  }
};

const removeItem = async (variantId: number) => {
  await cartStore.remove(variantId);
  cartItems.value = cartItems.value.filter((i) => i.variantId !== variantId);
};

const handleClearCart = async () => {
  await cartStore.clearCart();
  cartItems.value = [];
};

// Watch auth state changes
watch(isAuthenticated, async (newValue) => {
  isLoading.value = true;

  if (newValue) {
    await cartStore.syncWithServer();
  } else {
    cartStore.clear();
    await cartStore.init();
  }

  await loadCartItems();
  isLoading.value = false;
});

// SEO
useHead({
  title: t("cart.title"),
});
</script>
