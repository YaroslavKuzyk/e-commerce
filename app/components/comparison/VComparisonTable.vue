<template>
  <div
    class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg"
  >
    <table class="w-full border-collapse">
      <!-- Product Cards Header -->
      <thead>
        <tr>
          <th
            class="sticky left-0 z-10 bg-white dark:bg-gray-900 p-4 text-left font-medium text-gray-500 min-w-[200px] border-b border-r border-gray-200 dark:border-gray-700"
          >
            {{ $t("comparison.characteristic") }}
          </th>
          <th
            v-for="product in products"
            :key="product.id"
            class="p-0 min-w-[250px] border-b border-r last:border-r-0 border-gray-200 dark:border-gray-700 align-top"
          >
            <div class="relative p-4">
              <!-- Remove button -->
              <button
                class="absolute top-2 right-2 w-6 h-6 flex items-center justify-center text-gray-400 hover:text-red-500 transition-colors"
                @click="$emit('remove', product.id)"
              >
                <Trash2 class="w-4 h-4" />
              </button>

              <!-- Product image -->
              <div class="flex justify-center mb-3">
                <VSecureImage
                  v-if="product.main_image_file_id"
                  :file-id="product.main_image_file_id"
                  :alt="product.name"
                  img-class="h-32 object-contain"
                />
                <div
                  v-else
                  class="w-32 h-32 bg-gray-100 dark:bg-gray-800 rounded flex items-center justify-center"
                >
                  <Package class="w-12 h-12 text-gray-400" />
                </div>
              </div>

              <!-- Product name -->
              <h3
                class="font-semibold text-gray-900 dark:text-white text-sm line-clamp-2 mb-2 text-center"
              >
                {{ product.name }}
              </h3>

              <!-- Rating -->
              <div class="flex items-center justify-center gap-2 mb-3">
                <VRating
                  :model-value="product.average_rating || 0"
                  readonly
                  size="sm"
                />
                <span class="text-sm text-gray-500">{{
                  product.reviews_count || 0
                }}</span>
              </div>

              <!-- Price -->
              <div class="text-center mb-3">
                <div
                  v-if="
                    product.old_price &&
                    Number(product.old_price) >
                      Number(product.current_price || product.base_price)
                  "
                  class="text-sm text-gray-400 line-through"
                >
                  {{ formatPrice(product.old_price) }} грн
                </div>
                <div class="text-xl font-bold text-primary">
                  {{
                    formatPrice(product.current_price || product.base_price)
                  }}
                  грн
                </div>
              </div>

              <!-- Add to cart button -->
              <UButton
                :loading="loadingProductId === product.id"
                @click="handleAddToCart(product)"
              >
                <ShoppingCart class="w-4 h-4 mr-2" />
                {{ $t("cart.addToCart") }}
              </UButton>
            </div>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(row, index) in rows"
          :key="index"
          :class="[
            'border-b last:border-b-0 border-gray-200 dark:border-gray-700',
            row.isDifferent
              ? 'bg-yellow-50 dark:bg-yellow-900/20'
              : 'bg-white dark:bg-gray-900',
          ]"
        >
          <td
            class="sticky left-0 z-10 p-4 font-medium text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700"
            :class="
              row.isDifferent
                ? 'bg-yellow-50 dark:bg-yellow-900/20'
                : 'bg-white dark:bg-gray-900'
            "
          >
            {{ row.label }}
          </td>
          <td
            v-for="(value, vIndex) in row.values"
            :key="vIndex"
            class="p-4 text-center text-gray-900 dark:text-white border-r last:border-r-0 border-gray-200 dark:border-gray-700"
          >
            <span v-if="value !== null">{{ value }}</span>
            <span v-else class="text-gray-400">-</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts" setup>
import { Trash2, Package, ShoppingCart } from "lucide-vue-next";
import type { Product } from "~/models/product";
import type { ComparisonTableRow } from "~/services/ComparisonService";
import VSecureImage from "~/components/common/VSecureImage.vue";
import VRating from "~/components/common/VRating.vue";
import { useCartStore } from "~/stores/useCartStore";

interface Props {
  products: Product[];
  rows: ComparisonTableRow[];
}

defineProps<Props>();

defineEmits<{
  remove: [productId: number];
}>();

const cartStore = useCartStore();
const loadingProductId = ref<number | null>(null);

const formatPrice = (price: string | number): string => {
  const num = typeof price === "string" ? parseFloat(price) : price;
  return num.toLocaleString("uk-UA");
};

const handleAddToCart = async (product: Product) => {
  loadingProductId.value = product.id;
  try {
    await cartStore.add(product.id);
  } finally {
    loadingProductId.value = null;
  }
};
</script>
