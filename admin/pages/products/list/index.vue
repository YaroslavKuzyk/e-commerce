<template>
  <VSidebarContent :title="$t('products.title')">
    <template #toolbar>
      <div class="flex items-center justify-between w-full gap-2">
        <div class="flex items-center gap-2 w-full flex-wrap">
          <UInput
            v-model="filters.name"
            :placeholder="$t('products.searchByName')"
            class="w-[200px]"
          >
            <template #leading>
              <Search class="w-5 h-5" />
            </template>
          </UInput>
          <USelectMenu
            v-model="filters.status"
            :items="statusOptions"
            :placeholder="$t('common.status')"
            value-key="value"
            label-key="label"
            class="w-[150px]"
          >
            <template #trailing>
              <UButton
                v-if="filters.status"
                size="sm"
                variant="link"
                :aria-label="$t('common.clearFilters')"
                @click.stop="filters.status = null"
                color="neutral"
              >
                <CircleX class="w-4 h-4" />
              </UButton>
            </template>
          </USelectMenu>
          <USelectMenu
            v-model="filters.category_id"
            :items="categoryOptions"
            :placeholder="$t('products.category')"
            value-key="value"
            label-key="label"
            class="w-[180px]"
          >
            <template #trailing>
              <UButton
                v-if="filters.category_id"
                size="sm"
                variant="link"
                :aria-label="$t('common.clearFilters')"
                @click.stop="filters.category_id = null"
                color="neutral"
              >
                <CircleX class="w-4 h-4" />
              </UButton>
            </template>
          </USelectMenu>
          <USelectMenu
            v-model="filters.brand_id"
            :items="brandOptions"
            :placeholder="$t('products.brand')"
            value-key="value"
            label-key="label"
            class="w-[180px]"
          >
            <template #trailing>
              <UButton
                v-if="filters.brand_id"
                size="sm"
                variant="link"
                :aria-label="$t('common.clearFilters')"
                @click.stop="filters.brand_id = null"
                color="neutral"
              >
                <CircleX class="w-4 h-4" />
              </UButton>
            </template>
          </USelectMenu>
          <UButton
            v-if="hasActiveFilters"
            variant="ghost"
            @click="clearFilters"
          >
            <template #leading>
              <X class="w-5 h-5" />
            </template>
            {{ $t("common.clearFilters") }}
          </UButton>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <HasPermissions :required-permissions="['Create Product']">
            <UButton @click="router.push('/products/list/create')">
              <template #leading>
                <Plus class="w-4 h-4" />
              </template>
              {{ $t("products.add") }}
            </UButton>
          </HasPermissions>
        </div>
      </div>
    </template>

    <HasPermissions :required-permissions="['Read Products']">
      <!-- Delete Modal -->
      <VProductDeleteModal
        v-model:is-open="isDeleteModalOpen"
        :product="productToDelete"
        @refresh="refreshProducts"
      />

      <ProductsTable
        :products="productsData || []"
        :loading="pending"
        @edit="handleEdit"
        @delete="handleDelete"
      />
    </HasPermissions>

    <template #pagination>
      <VPagination
        :meta="meta"
        @update:page="(page) => filters.page = page"
        @update:per-page="(perPage) => filters.per_page = perPage"
      />
    </template>
  </VSidebarContent>
</template>

<script setup lang="ts">
import { Search, CircleX, X, Plus } from "lucide-vue-next";
import HasPermissions from "~/components/common/VHasPermissions.vue";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import ProductsTable from "~/components/products/list/tables/ProductsTable.vue";
import VProductDeleteModal from "~/components/products/list/modals/VProductDeleteModal.vue";
import VPagination from "~/components/common/VPagination.vue";
import type { Product, ProductFilters, ProductStatus } from "~/models/product";
import type { ProductCategory } from "~/models/productCategory";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Read Products"],
});

const { t } = useI18n();
const router = useRouter();
const productStore = useProductStore();
const productCategoryStore = useProductCategoryStore();
const productBrandStore = useProductBrandStore();

const isDeleteModalOpen = ref(false);
const productToDelete = ref<Product | null>(null);

const filters = ref({
  name: "",
  status: null as ProductStatus | null,
  category_id: null as number | null,
  brand_id: null as number | null,
  page: 1,
  per_page: 15,
});

const statusOptions = computed(() => [
  { label: t("common.published"), value: "published" },
  { label: t("common.draft"), value: "draft" },
]);

// Fetch categories and brands for filters
const { data: categoriesData } = await productCategoryStore.fetchProductCategories();
const { data: brandsData } = await productBrandStore.fetchProductBrands();

const categoryOptions = computed(() => {
  const flatten = (cats: ProductCategory[], level = 0): { label: string; value: number }[] => {
    return cats.reduce((acc: { label: string; value: number }[], cat) => {
      const prefix = level > 0 ? '— '.repeat(level) : '';
      acc.push({
        label: prefix + cat.name,
        value: cat.id,
      });
      if (cat.subcategories && cat.subcategories.length > 0) {
        acc.push(...flatten(cat.subcategories, level + 1));
      }
      return acc;
    }, []);
  };
  return flatten(categoriesData.value || []);
});

const brandOptions = computed(() => {
  return (brandsData.value?.data || []).map(brand => ({
    label: brand.name,
    value: brand.id,
  }));
});

const {
  data: productsData,
  meta,
  pending,
  hasActiveFilters,
  clearFilters,
  refresh: internalRefresh,
} = await usePaginationList<typeof filters.value, Product>({
  key: 'products-list',
  filters,
  fetchFunction: (filters?: ProductFilters) => productStore.fetchProductsPromise(filters),
  debounceFields: ["name"],
});

const refreshProducts = async () => {
  await internalRefresh();
};

const handleEdit = (product: Product) => {
  router.push(`/products/list/${product.id}/edit`);
};

const handleDelete = (product: Product) => {
  productToDelete.value = product;
  isDeleteModalOpen.value = true;
};
</script>
