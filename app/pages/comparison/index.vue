<template>
  <UContainer class="py-8">
    <!-- Breadcrumbs -->
    <div class="mb-6">
      <nav class="flex items-center gap-2 text-sm text-gray-500">
        <NuxtLink to="/" class="hover:text-primary">{{ $t("common.home") }}</NuxtLink>
        <ChevronRight class="w-4 h-4" />
        <span class="text-gray-900 dark:text-white">{{ $t("comparison.listsTitle") }}</span>
      </nav>
    </div>

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center gap-3">
        <div class="w-1 h-8 bg-primary rounded-full"></div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ $t("comparison.listsTitle") }}
        </h1>
        <span v-if="comparisonStore.count > 0" class="text-gray-500">
          ({{ comparisonStore.count }})
        </span>
      </div>

      <!-- Clear All Button -->
      <UButton
        v-if="comparisonStore.count > 0"
        variant="ghost"
        color="error"
        @click="clearAll"
      >
        <template #leading>
          <Trash2 class="w-4 h-4" />
        </template>
        {{ $t("comparison.clearComparison") }}
      </UButton>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center py-16">
      <div class="flex flex-col items-center gap-3">
        <UButton loading variant="ghost" size="xl" />
        <span class="text-sm text-gray-500">{{ $t("comparison.loading") }}</span>
      </div>
    </div>

    <!-- Guest State Notice -->
    <div v-else-if="!isAuthenticated && comparisonStore.count > 0" class="mb-6">
      <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
        <p class="text-sm text-blue-700 dark:text-blue-300">
          {{ $t("comparison.guestNotice") }}
        </p>
      </div>
    </div>

    <!-- Comparison Lists by Category -->
    <div v-if="!isLoading && comparisonLists.length > 0" class="space-y-6">
      <div
        v-for="list in comparisonLists"
        :key="list.category.id"
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
      >
        <!-- Category Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
          <div class="flex items-center gap-3">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              {{ list.category.name }}
            </h2>
            <UBadge variant="subtle" color="neutral" size="sm">
              {{ list.products_count }} {{ $t("common.products") }}
            </UBadge>
          </div>

          <div class="flex items-center gap-2">
            <NuxtLink :to="localePath(`/comparison/${list.category.slug}`)">
              <UButton color="primary" size="sm">
                <template #leading>
                  <GitCompare class="w-4 h-4" />
                </template>
                {{ $t("comparison.compare") }}
              </UButton>
            </NuxtLink>
            <UButton
              variant="ghost"
              color="neutral"
              size="sm"
              @click="clearCategory(list.category.id)"
            >
              <X class="w-4 h-4" />
            </UButton>
          </div>
        </div>

        <!-- Products Grid -->
        <div class="p-6">
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            <!-- Product Cards -->
            <div
              v-for="product in list.products"
              :key="product.id"
              class="group relative bg-gray-50 dark:bg-gray-900 rounded-lg p-3 hover:shadow-md transition-all"
            >
              <!-- Remove Button -->
              <button
                class="absolute top-2 right-2 z-10 w-6 h-6 rounded-full bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors opacity-0 group-hover:opacity-100"
                @click.prevent="removeProduct(product.id)"
              >
                <X class="w-3.5 h-3.5" />
              </button>

              <!-- Product Card -->
              <NuxtLink :to="buildProductUrlFromProduct(product)" class="block">
                <div class="aspect-square mb-3 flex items-center justify-center">
                  <VSecureImage
                    v-if="product.main_image_file_id"
                    :fileId="product.main_image_file_id"
                    imgClass="w-full h-full object-contain"
                  />
                  <div v-else class="w-full h-full bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center">
                    <Package class="w-10 h-10 text-gray-400" />
                  </div>
                </div>
                <h3 class="text-sm font-medium line-clamp-2 mb-2 group-hover:text-primary transition-colors">
                  {{ product.name }}
                </h3>
                <p class="text-sm font-bold text-primary">
                  {{ formatPrice(product.current_price || product.base_price) }} грн
                </p>
              </NuxtLink>
            </div>

            <!-- Add More Button -->
            <NuxtLink
              :to="localePath(`/category/${list.category.slug}`)"
              class="aspect-square flex flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-200 dark:border-gray-700 hover:border-primary hover:bg-primary/5 transition-all cursor-pointer"
            >
              <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <Plus class="w-5 h-5 text-gray-400" />
              </div>
              <span class="text-xs text-gray-500 text-center px-2">{{ $t("comparison.addMoreModel") }}</span>
            </NuxtLink>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!isLoading" class="text-center py-16">
      <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
        <Scale class="w-12 h-12 text-gray-400" />
      </div>
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
        {{ $t("comparison.emptyTitle") }}
      </h2>
      <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
        {{ $t("comparison.emptyDescription") }}
      </p>
      <NuxtLink :to="localePath('/store')">
        <UButton size="lg">
          <template #leading>
            <ShoppingBag class="w-5 h-5" />
          </template>
          {{ $t("comparison.goToStore") }}
        </UButton>
      </NuxtLink>
    </div>
  </UContainer>
</template>

<script lang="ts" setup>
import { Scale, ChevronRight, ShoppingBag, Trash2, X, Plus, Package, GitCompare } from "lucide-vue-next";
import { useComparisonStore, type ComparisonCategory } from "~/stores/useComparisonStore";
import type { Product } from "~/models/product";
import VSecureImage from "~/components/common/VSecureImage.vue";
import { buildProductUrl } from "~/utils/urlBuilder";

const { t } = useI18n();
const localePath = useLocalePath();
const comparisonStore = useComparisonStore();
const client = useSanctumClient();
const { user } = useSanctumAuth();

const isAuthenticated = computed(() => !!user.value);
const isLoading = ref(true);
const comparisonLists = ref<ComparisonCategory[]>([]);

// Initialize comparison
onMounted(async () => {
  await comparisonStore.init();
  await loadComparisonLists();
  isLoading.value = false;
});

// Load comparison lists
const loadComparisonLists = async () => {
  if (isAuthenticated.value) {
    comparisonLists.value = await comparisonStore.getComparisonLists();
  } else {
    // For guest users, load products by IDs grouped by category
    await loadGuestComparisonLists();
  }
};

// Load comparison lists for guest users
const loadGuestComparisonLists = async () => {
  const productIds = Array.from(comparisonStore.comparisonItems.keys());
  if (productIds.length === 0) return;

  try {
    const idsParam = productIds.join(",");
    const response = await client<{ success: boolean; data: Product[] }>(
      `/api/products?ids=${idsParam}&limit=50`
    );

    // Group products by root category
    const categoryMap = new Map<number, { category: { id: number; name: string; slug: string }; products: Product[] }>();

    for (const product of response.data || []) {
      const categoryId = comparisonStore.comparisonItems.get(product.id);
      if (categoryId === undefined) continue;

      if (!categoryMap.has(categoryId)) {
        // Get root category from product
        let rootCategory = product.category;
        while (rootCategory?.parent) {
          rootCategory = rootCategory.parent;
        }

        categoryMap.set(categoryId, {
          category: {
            id: categoryId,
            name: rootCategory?.name || "Unknown",
            slug: rootCategory?.slug || "unknown",
          },
          products: [],
        });
      }

      categoryMap.get(categoryId)!.products.push(product);
    }

    comparisonLists.value = Array.from(categoryMap.values()).map((item) => ({
      category: item.category,
      products_count: item.products.length,
      products: item.products,
    }));
  } catch (error) {
    console.error("Failed to load guest comparison lists:", error);
  }
};

// Build product URL
const buildProductUrlFromProduct = (product: Product) => {
  const path: string[] = [];
  let currentCategory = product.category;

  while (currentCategory) {
    path.unshift(currentCategory.slug);
    currentCategory = currentCategory.parent;
  }

  return buildProductUrl(path, product.slug);
};

// Format price
const formatPrice = (price: string | number) => {
  return Number(price).toLocaleString("uk-UA");
};

// Remove product from comparison
const removeProduct = async (productId: number) => {
  await comparisonStore.remove(productId);
  await loadComparisonLists();
};

// Clear category comparison
const clearCategory = async (categoryId: number) => {
  await comparisonStore.clearComparison(categoryId);
  await loadComparisonLists();
};

// Clear all comparisons
const clearAll = async () => {
  await comparisonStore.clearComparison();
  comparisonLists.value = [];
};

// Watch auth state changes
watch(isAuthenticated, async (newValue) => {
  isLoading.value = true;

  if (newValue) {
    await comparisonStore.syncWithServer();
  } else {
    comparisonStore.clear();
    await comparisonStore.init();
  }

  await loadComparisonLists();
  isLoading.value = false;
});

// SEO
useHead({
  title: t("comparison.listsTitle"),
});
</script>
