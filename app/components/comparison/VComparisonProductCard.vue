<template>
  <div
    class="w-[220px] flex-shrink-0 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4"
  >
    <!-- Remove Button -->
    <div class="flex justify-end mb-2">
      <UButton variant="ghost" color="error" size="xs" @click="$emit('remove')">
        <X class="w-4 h-4" />
      </UButton>
    </div>

    <!-- Product Image -->
    <div class="flex justify-center mb-4">
      <VSecureImage
        v-if="product.main_image_file_id"
        :fileId="product.main_image_file_id"
        imgClass="w-[120px] h-[120px] object-contain"
      />
      <div
        v-else
        class="w-[120px] h-[120px] bg-gray-100 rounded flex items-center justify-center"
      >
        <Package class="w-8 h-8 text-gray-400" />
      </div>
    </div>

    <!-- Product Name -->
    <NuxtLink :to="productUrl" class="block mb-2">
      <h3
        class="font-semibold text-gray-900 dark:text-white line-clamp-2 hover:text-primary"
      >
        {{ product.name }}
      </h3>
    </NuxtLink>

    <!-- Rating -->
    <div class="flex items-center gap-2 mb-3">
      <VRating :model-value="product.average_rating || 0" readonly size="sm" />
      <span class="text-sm text-gray-500">
        {{ product.reviews_count || 0 }}
      </span>
    </div>

    <!-- Price -->
    <div class="mb-4">
      <div v-if="hasDiscount" class="text-sm text-gray-500 line-through">
        {{ formatPrice(product.base_price) }} грн
      </div>
      <div class="text-xl font-bold text-primary">
        {{ formatPrice(product.current_price || product.base_price) }} грн
      </div>
    </div>

    <!-- Add to Cart -->
    <UButton
      block
      :color="isInCart ? 'primary' : 'neutral'"
      :loading="isAddingToCart"
      @click="addToCart"
    >
      <ShoppingCart class="w-4 h-4 mr-2" />
      {{ isInCart ? $t("cart.inCart") : $t("cart.addToCart") }}
    </UButton>
  </div>
</template>

<script lang="ts" setup>
import { X, Package, ShoppingCart } from "lucide-vue-next";
import type { Product } from "~/models/product";
import { useCartStore } from "~/stores/useCartStore";
import { buildProductUrl } from "~/utils/urlBuilder";
import VSecureImage from "~/components/common/VSecureImage.vue";
import VRating from "~/components/common/VRating.vue";

interface Props {
  product: Product;
}

const props = defineProps<Props>();
defineEmits<{
  (e: "remove"): void;
}>();

const cartStore = useCartStore();

const isInCart = computed(() => cartStore.isInCart(props.product.id));
const isAddingToCart = ref(false);

const hasDiscount = computed(() => {
  return props.product.discount_price || props.product.discount_percent;
});

const productUrl = computed(() => {
  const categoryPath: string[] = [];
  let category = props.product.category;
  while (category) {
    categoryPath.unshift(category.slug);
    category = category.parent;
  }
  return buildProductUrl(categoryPath, props.product.slug);
});

const formatPrice = (price: string | number) => {
  return Number(price).toLocaleString("uk-UA");
};

const addToCart = async () => {
  if (isAddingToCart.value) return;
  isAddingToCart.value = true;
  try {
    await cartStore.add(props.product.id);
  } finally {
    isAddingToCart.value = false;
  }
};
</script>
