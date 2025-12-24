<template>
  <UCard class="w-full max-w-2xl">
    <template #header>
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold">{{ $t("comparison.addMore") }}</h3>
        <UButton variant="ghost" color="neutral" size="sm" @click="$emit('close')">
          <X class="w-4 h-4" />
        </UButton>
      </div>
    </template>

    <!-- Search -->
    <div class="mb-4">
      <UInput
        v-model="searchQuery"
        :placeholder="$t('common.searchPlaceholder')"
        icon="i-lucide-search"
        size="lg"
      />
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex justify-center py-8">
      <UButton loading variant="ghost" size="xl" />
    </div>

    <!-- Products List -->
    <div v-else-if="filteredProducts.length > 0" class="space-y-2 max-h-96 overflow-y-auto">
      <div
        v-for="product in filteredProducts"
        :key="product.id"
        class="flex items-center gap-4 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer"
        @click="handleSelect(product)"
      >
        <VSecureImage
          v-if="product.main_image_file_id"
          :fileId="product.main_image_file_id"
          imgClass="w-16 h-16 object-contain rounded"
        />
        <div v-else class="w-16 h-16 bg-gray-100 rounded flex items-center justify-center">
          <Package class="w-6 h-6 text-gray-400" />
        </div>
        <div class="flex-1 min-w-0">
          <h4 class="font-medium text-gray-900 dark:text-white line-clamp-1">
            {{ product.name }}
          </h4>
          <p class="text-sm text-primary font-semibold">
            {{ formatPrice(product.current_price || product.base_price) }} грн
          </p>
        </div>
        <UButton variant="soft" color="primary" size="sm">
          <Plus class="w-4 h-4 mr-1" />
          {{ $t("comparison.add") }}
        </UButton>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-8 text-gray-500">
      {{ $t("common.noResults") }}
    </div>
  </UCard>
</template>

<script lang="ts" setup>
import { X, Package, Plus } from "lucide-vue-next";
import type { Product } from "~/models/product";
import VSecureImage from "~/components/common/VSecureImage.vue";

interface Props {
  categoryId: number;
  excludedIds: number[];
}

const props = defineProps<Props>();
const emit = defineEmits<{
  (e: "add", product: Product): void;
  (e: "close"): void;
}>();

const client = useSanctumClient();
const searchQuery = ref("");
const isLoading = ref(true);
const products = ref<Product[]>([]);

const filteredProducts = computed(() => {
  const query = searchQuery.value.toLowerCase().trim();
  let filtered = products.value.filter(
    (p) => !props.excludedIds.includes(p.id)
  );

  if (query) {
    filtered = filtered.filter((p) =>
      p.name.toLowerCase().includes(query)
    );
  }

  return filtered;
});

const formatPrice = (price: string | number) => {
  return Number(price).toLocaleString("uk-UA");
};

const handleSelect = (product: Product) => {
  emit("add", product);
};

const loadProducts = async () => {
  try {
    isLoading.value = true;
    const response = await client<{ success: boolean; data: Product[] }>(
      `/api/products?category_id=${props.categoryId}&limit=50`
    );
    products.value = response.data;
  } catch (error) {
    console.error("Failed to load products:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  loadProducts();
});
</script>
