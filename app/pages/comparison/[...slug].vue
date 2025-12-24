<template>
  <UContainer class="py-8">
    <!-- Breadcrumbs -->
    <div class="mb-6">
      <nav class="flex items-center gap-2 text-sm text-gray-500">
        <NuxtLink :to="localePath('/')" class="hover:text-primary">
          {{ $t("common.home") }}
        </NuxtLink>
        <ChevronRight class="w-4 h-4" />
        <NuxtLink :to="localePath('/comparison')" class="hover:text-primary">
          {{ $t("comparison.title") }}
        </NuxtLink>
        <ChevronRight class="w-4 h-4" />
        <span class="text-gray-900 dark:text-white">{{ categoryName }}</span>
      </nav>
    </div>

    <!-- Page Header -->
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8"
    >
      <div class="flex items-center gap-3">
        <div class="w-1 h-8 bg-primary rounded-full"></div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ $t("comparison.comparing") }} {{ categoryName }}
        </h1>
      </div>

      <!-- Only Differences Toggle -->
      <UCheckbox
        v-model="showOnlyDifferences"
        :label="$t('comparison.onlyDifferences')"
      />
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex justify-center py-16">
      <UButton loading variant="ghost" size="xl" />
    </div>

    <!-- Comparison Content -->
    <div v-else-if="products && products.length > 0">
      <!-- Comparison Table with Product Cards -->
      <VComparisonTable
        :products="products"
        :rows="displayedRows"
        @remove="removeProduct"
      />
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-16">
      <p class="text-gray-500">{{ $t("comparison.noProducts") }}</p>
      <NuxtLink :to="localePath('/store')" class="mt-4 inline-block">
        <UButton>{{ $t("comparison.goToStore") }}</UButton>
      </NuxtLink>
    </div>

    </UContainer>
</template>

<script lang="ts" setup>
import { ChevronRight } from "lucide-vue-next";
import { useComparisonStore } from "~/stores/useComparisonStore";
import {
  ComparisonService,
  type ComparisonTableRow,
} from "~/services/ComparisonService";
import type { Product } from "~/models/product";
import VComparisonTable from "~/components/comparison/VComparisonTable.vue";

const route = useRoute();
const { t } = useI18n();
const localePath = useLocalePath();
const comparisonStore = useComparisonStore();

const categorySlug = computed(() => {
  const slug = route.params.slug;
  if (Array.isArray(slug)) {
    return slug.join("/");
  }
  return slug || "";
});

const isLoading = ref(true);
const products = ref<Product[]>([]);
const categoryName = ref<string>("");
const showOnlyDifferences = ref(false);
const tableRows = ref<ComparisonTableRow[]>([]);

// Check for shared comparison URL
const sharedIds = computed(() => {
  const ids = route.query.ids;
  if (typeof ids === "string") {
    return ComparisonService.parseShareUrl(`?ids=${ids}`);
  }
  return [];
});

const displayedRows = computed(() => {
  if (showOnlyDifferences.value) {
    return ComparisonService.filterDifferencesOnly(tableRows.value);
  }
  return tableRows.value;
});

onMounted(async () => {
  await comparisonStore.init();
  await loadComparison();
});

const loadComparison = async () => {
  isLoading.value = true;

  try {
    // If shared link with IDs
    if (sharedIds.value.length > 0) {
      products.value = await ComparisonService.getProductsByIds(
        sharedIds.value
      );
      const firstProduct = products.value[0];
      if (firstProduct && firstProduct.category) {
        categoryName.value = firstProduct.category.name;
      }
    } else {
      // Load from store
      const data = await comparisonStore.getCategoryProducts(categorySlug.value);
      if (data) {
        products.value = data.variants;
        categoryName.value = data.category.name;
      }
    }

    // Build comparison table
    tableRows.value = ComparisonService.buildComparisonTable(products.value);
  } catch (error) {
    console.error("Failed to load comparison:", error);
  } finally {
    isLoading.value = false;
  }
};

const removeProduct = async (productId: number) => {
  await comparisonStore.remove(productId);
  products.value = products.value.filter((p) => p.id !== productId);
  tableRows.value = ComparisonService.buildComparisonTable(products.value);
};

useHead({
  title: computed(() => `${t("comparison.comparing")} ${categoryName.value}`),
});
</script>
