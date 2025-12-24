<template>
  <div v-if="isLoading" class="flex justify-center py-12">
    <UButton loading variant="ghost" />
  </div>

  <div
    v-else-if="products && products.length > 0"
    class="flex gap-6 flex-wrap"
  >
    <VProductThumb
      v-for="product in products"
      :key="product.id"
      :product="product"
      :class="itemWidthClass"
    />
  </div>

  <div v-else class="text-center py-12">
    <Package class="w-16 h-16 text-gray-400 mx-auto mb-4" />
    <h3 class="text-xl font-semibold mb-2">Товарів не знайдено</h3>
    <p class="text-dimmed">Спробуйте змінити параметри пошуку</p>
    <UButton
      v-if="hasFilters"
      variant="soft"
      class="mt-4"
      @click="$emit('reset')"
    >
      Скинути фільтри
    </UButton>
  </div>
</template>

<script setup lang="ts">
import { Package } from "lucide-vue-next";
import VProductThumb from "./VProductThumb.vue";
import type { Product } from "~/models/product";

interface Props {
  products: Product[];
  isLoading: boolean;
  hasFilters?: boolean;
  columns?: 3 | 4;
}

const props = withDefaults(defineProps<Props>(), {
  hasFilters: false,
  columns: 3,
});

const itemWidthClass = computed(() => {
  // gap-6 = 24px, so for 3 columns: (100% - 2*24px) / 3 = 33.333% - 16px
  // for 4 columns: (100% - 3*24px) / 4 = 25% - 18px
  return props.columns === 4 ? "w-[calc(25%-18px)]" : "w-[calc(33.333%-16px)]";
});

defineEmits<{
  reset: [];
}>();
</script>
