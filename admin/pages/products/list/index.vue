<template>
  <VSidebarContent title="Продукти">
    <template #toolbar>
      <div class="flex items-center justify-between w-full gap-2">
        <div class="flex items-center gap-2 w-full flex-wrap">
          <UInput
            v-model="filters.name"
            placeholder="Пошук за назвою"
            class="w-[200px]"
          >
            <template #leading>
              <Search class="w-5 h-5" />
            </template>
          </UInput>
          <USelectMenu
            v-model="filters.status"
            :items="statusOptions"
            placeholder="Статус"
            value-key="value"
            label-key="label"
            class="w-[150px]"
          >
            <template #trailing>
              <UButton
                v-if="filters.status"
                size="sm"
                variant="link"
                aria-label="Очистити"
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
            placeholder="Категорія"
            value-key="value"
            label-key="label"
            class="w-[180px]"
          >
            <template #trailing>
              <UButton
                v-if="filters.category_id"
                size="sm"
                variant="link"
                aria-label="Очистити"
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
            placeholder="Бренд"
            value-key="value"
            label-key="label"
            class="w-[180px]"
          >
            <template #trailing>
              <UButton
                v-if="filters.brand_id"
                size="sm"
                variant="link"
                aria-label="Очистити"
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
            Очистити фільтри
          </UButton>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <HasPermissions :required-permissions="['Create Product']">
            <UButton @click="router.push('/products/list/create')">
              <template #leading>
                <Plus class="w-4 h-4" />
              </template>
              Додати продукт
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

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Read Products"],
});

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

const statusOptions = [
  { label: "Опубліковано", value: "published" },
  { label: "Чернетка", value: "draft" },
];

// Fetch categories and brands for filters
const { data: categoriesData } = await productCategoryStore.fetchProductCategories();
const { data: brandsData } = await productBrandStore.fetchProductBrands();

const categoryOptions = computed(() => {
  return (categoriesData.value || []).map(cat => ({
    label: cat.name,
    value: cat.id,
  }));
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
