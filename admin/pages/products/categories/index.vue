<template>
  <VSidebarContent :title="$t('productCategories.title')">
    <template #toolbar>
      <div class="flex items-center justify-between w-full gap-2">
        <div class="flex items-center gap-2 w-full">
          <UInput
            v-model="filters.search"
            :placeholder="$t('productCategories.searchByName')"
            class="w-[250px]"
          >
            <template #leading>
              <Search class="w-5 h-5" />
            </template>
          </UInput>
          <UInput
            v-model="filters.slugSearch"
            :placeholder="$t('productCategories.searchBySlug')"
            class="w-[200px]"
          >
            <template #leading>
              <Hash class="w-5 h-5" />
            </template>
          </UInput>
          <USelect
            v-model="filters.status"
            :items="statusOptions"
            :placeholder="$t('common.status')"
            class="w-[150px]"
          />
          <UButton
            v-if="hasActiveFilters"
            variant="ghost"
            @click="resetFilters"
          >
            <template #leading>
              <X class="w-5 h-5" />
            </template>
            {{ $t("common.clearFilters") }}
          </UButton>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <HasPermissions :required-permissions="['Create Product Category']">
            <UButton @click="router.push('/products/categories/create')">
              <template #leading>
                <Plus class="w-4 h-4" />
              </template>
              {{ $t("productCategories.add") }}
            </UButton>
          </HasPermissions>
        </div>
      </div>
    </template>

    <HasPermissions :required-permissions="['Read Product Categories']">
      <!-- Delete Modal -->
      <VProductCategoryDeleteModal
        v-model:is-open="isDeleteModalOpen"
        :category="categoryToDelete"
        @refresh="refreshCategories"
      />

      <ProductCategoriesTable
        :categories="categories"
        :loading="loading"
        :has-active-filters="hasActiveFilters"
        @edit="handleEdit"
        @delete="handleDelete"
        @create-subcategory="handleCreateSubcategory"
      />
    </HasPermissions>
  </VSidebarContent>
</template>

<script setup lang="ts">
import HasPermissions from "~/components/common/VHasPermissions.vue";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import ProductCategoriesTable from "~/components/products/categories/tables/ProductCategoriesTable.vue";
import VProductCategoryDeleteModal from "~/components/products/categories/modals/VProductCategoryDeleteModal.vue";
import type { ProductCategory } from "~/models/productCategory";
import { Plus, Search, Hash, X } from "lucide-vue-next";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Read Product Categories"],
});

const { t } = useI18n();
const router = useRouter();
const productCategoryStore = useProductCategoryStore();

const isDeleteModalOpen = ref(false);
const categoryToDelete = ref<ProductCategory | null>(null);

// Filters
const filters = ref({
  search: "",
  slugSearch: "",
  status: null as string | null,
});

const statusOptions = computed(() => [
  { label: t("common.published"), value: "published" },
  { label: t("common.draft"), value: "draft" },
]);

const hasActiveFilters = computed(() => {
  return filters.value.search !== "" ||
         filters.value.slugSearch !== "" ||
         filters.value.status !== null;
});

const resetFilters = () => {
  filters.value.search = "";
  filters.value.slugSearch = "";
  filters.value.status = null;
};

const {
  data: categoriesData,
  refresh: refreshCategoriesData,
  status,
} = await productCategoryStore.fetchProductCategories();

// Filter categories recursively
const filterCategoriesRecursive = (categories: ProductCategory[]): ProductCategory[] => {
  return categories.filter(category => {
    // Check if current category matches filters
    const matchesSearch = !filters.value.search ||
      category.name.toLowerCase().includes(filters.value.search.toLowerCase());

    const matchesSlug = !filters.value.slugSearch ||
      category.slug.toLowerCase().includes(filters.value.slugSearch.toLowerCase());

    const matchesStatus = !filters.value.status ||
      category.status === filters.value.status;

    // Check if any subcategory matches (recursive)
    const hasMatchingSubcategory = category.subcategories && category.subcategories.length > 0
      ? filterCategoriesRecursive(category.subcategories).length > 0
      : false;

    // Include category if it matches OR if it has matching subcategories
    if (matchesSearch && matchesSlug && matchesStatus) {
      return true;
    }

    if (hasMatchingSubcategory) {
      return true;
    }

    return false;
  }).map(category => {
    // If category has subcategories, filter them too
    if (category.subcategories && category.subcategories.length > 0) {
      return {
        ...category,
        subcategories: filterCategoriesRecursive(category.subcategories)
      };
    }
    return category;
  });
};

const categories = computed(() => {
  if (!categoriesData.value) return [];

  if (!hasActiveFilters.value) {
    return categoriesData.value;
  }

  return filterCategoriesRecursive(categoriesData.value);
});

const loading = computed(() => status.value === "pending");

const handleEdit = (category: ProductCategory) => {
  router.push(`/products/categories/${category.id}/edit`);
};

const handleCreateSubcategory = (category: ProductCategory) => {
  router.push(`/products/categories/create?parent_id=${category.id}`);
};

const handleDelete = (category: ProductCategory) => {
  categoryToDelete.value = category;
  isDeleteModalOpen.value = true;
};

const refreshCategories = async () => {
  await refreshCategoriesData();
};
</script>
