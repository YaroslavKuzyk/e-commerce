<template>
  <VSidebarContent :title="$t('productBrands.title')">
    <template #toolbar>
      <div class="flex items-center justify-between w-full gap-2">
        <div class="flex items-center gap-2 w-full">
          <UInput
            v-model="filters.name"
            :placeholder="$t('productBrands.searchByName')"
            class="w-[200px]"
          >
            <template #leading>
              <Search class="w-5 h-5" />
            </template>
          </UInput>
          <UInput
            v-model="filters.slug"
            :placeholder="$t('productBrands.searchBySlug')"
            class="w-[200px]"
          >
            <template #leading>
              <Hash class="w-5 h-5" />
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
                :aria-label="$t('common.clear')"
                @click.stop="filters.status = null"
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
          <HasPermissions :required-permissions="['Create Product Brand']">
            <UButton @click="router.push('/products/brands/create')">
              <template #leading>
                <Plus class="w-4 h-4" />
              </template>
              {{ $t("productBrands.add") }}
            </UButton>
          </HasPermissions>
        </div>
      </div>
    </template>

    <HasPermissions :required-permissions="['Read Product Brands']">
      <!-- Delete Modal -->
      <VProductBrandDeleteModal
        v-model:is-open="isDeleteModalOpen"
        :brand="brandToDelete"
        @refresh="refreshBrands"
      />

      <ProductBrandsTable
        :brands="brandsData || []"
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
import { Search, Hash, CircleX, X, Plus } from "lucide-vue-next";
import HasPermissions from "~/components/common/VHasPermissions.vue";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import ProductBrandsTable from "~/components/products/brands/tables/ProductBrandsTable.vue";
import VProductBrandDeleteModal from "~/components/products/brands/modals/VProductBrandDeleteModal.vue";
import VPagination from "~/components/common/VPagination.vue";
import type { ProductBrand, ProductBrandFilters, ProductBrandStatus } from "~/models/productBrand";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Read Product Brands"],
});

const { t } = useI18n();
const router = useRouter();
const productBrandStore = useProductBrandStore();

const isDeleteModalOpen = ref(false);
const brandToDelete = ref<ProductBrand | null>(null);

const filters = ref({
  name: "",
  slug: "",
  status: null as ProductBrandStatus | null,
  page: 1,
  per_page: 15,
});

const statusOptions = computed(() => [
  { label: t("common.published"), value: "published" },
  { label: t("common.draft"), value: "draft" },
]);

const {
  data: brandsData,
  meta,
  pending,
  hasActiveFilters,
  clearFilters,
  refresh: internalRefresh,
} = await usePaginationList<typeof filters.value, ProductBrand>({
  key: 'product-brands-list',
  filters,
  fetchFunction: (filters?: ProductBrandFilters) => productBrandStore.fetchProductBrandsPromise(filters),
  debounceFields: ["name", "slug"],
});

const refreshBrands = async () => {
  await internalRefresh();
};

const handleEdit = (brand: ProductBrand) => {
  router.push(`/products/brands/${brand.id}/edit`);
};

const handleDelete = (brand: ProductBrand) => {
  brandToDelete.value = brand;
  isDeleteModalOpen.value = true;
};
</script>
