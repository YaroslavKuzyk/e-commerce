<template>
  <UPage>
    <UContainer>
      <VBreadcrumbs
        v-if="breadcrumbs.length"
        :items="breadcrumbs"
        class="pt-6"
      />

      <VSubcategoryCards
        v-if="currentCategory?.subcategories?.length"
        :parent-name="currentCategory.name"
        :parent-slug="categoryPath.join('/')"
        :subcategories="currentCategory.subcategories"
        class="pt-6"
      />

      <div class="py-6">
        <VCategoryHeader
          :category-name="currentCategory?.name"
          :total="meta?.total"
          v-model:search="searchQuery"
          v-model:sort="selectedSort"
          :sort-options="sortOptions"
          :has-filters="hasFilters"
          @search="debouncedSearch"
          @sort-change="onSortChange"
          @reset="resetFilters"
        />

        <VActiveFilters
          v-if="hasFilters"
          :search-query="searchQuery"
          :filters="parsedFilters"
          :available-filters="availableFilters"
          @remove-search="removeSearch"
          @remove-brand="removeBrand"
          @remove-price="removePriceRange"
          @remove-attribute="removeAttributeValue"
          @remove-in-stock="removeInStock"
        />

        <div class="flex gap-6">
          <aside class="w-64 shrink-0">
            <VProductFiltersSlug
              :filters="parsedFilters"
              :available-filters="availableFilters"
              @update:filters="onFiltersChange"
            />
          </aside>

          <div class="flex-1">
            <VProductsGrid
              :products="products"
              :is-loading="isLoading"
              :has-filters="hasFilters"
              @reset="resetFilters"
            />

            <div
              v-if="meta && meta.last_page > 1"
              class="flex justify-center mt-8"
            >
              <UPagination
                v-model="currentPage"
                :page-count="meta.per_page"
                :total="meta.total"
              />
            </div>
          </div>
        </div>
      </div>
    </UContainer>
  </UPage>
</template>

<script setup lang="ts">
import { useDebounceFn } from "@vueuse/core";
import VBreadcrumbs from "~/components/common/VBreadcrumbs.vue";
import VSubcategoryCards from "~/components/category/VSubcategoryCards.vue";
import VCategoryHeader from "~/components/category/VCategoryHeader.vue";
import VActiveFilters from "~/components/category/VActiveFilters.vue";
import VProductFiltersSlug from "~/components/product/filters/VProductFiltersSlug.vue";
import VProductsGrid from "~/components/product/list/VProductsGrid.vue";

const {
  currentCategory,
  breadcrumbs,
  products,
  meta,
  availableFilters,
  parsedFilters,
  searchQuery,
  selectedSort,
  isLoading,
  currentPage,
  categoryPath,
  hasFilters,
  initializeData,
  fetchProducts,
  onFiltersChange,
  onSortChange,
  resetFilters,
  removeSearch,
  removeBrand,
  removePriceRange,
  removeAttributeValue,
  removeInStock,
} = useCategoryPage();

const sortOptions = [
  { value: "created_at", label: "Новинки" },
  { value: "name", label: "За назвою" },
  { value: "price_asc", label: "Від дешевих до дорогих" },
  { value: "price_desc", label: "Від дорогих до дешевих" },
];

const route = useRoute();

const debouncedSearch = useDebounceFn(() => {
  currentPage.value = 1;
  fetchProducts();
}, 500);

onMounted(() => initializeData());

watch(
  () => route.params.slug,
  () => initializeData()
);
watch(currentPage, () => fetchProducts());

useHead({
  title: computed(() => currentCategory.value?.name || "Каталог"),
});
</script>
