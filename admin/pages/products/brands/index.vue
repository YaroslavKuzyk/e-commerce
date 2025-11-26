<template>
  <VSidebarContent title="Бренди продуктів">
    <template #toolbar>
      <div class="flex items-center justify-between w-full gap-2">
        <div class="flex items-center gap-2 w-full">
          <UInput
            v-model="filters.search"
            placeholder="Пошук за назвою"
            class="w-[250px]"
          >
            <template #leading>
              <Search class="w-5 h-5" />
            </template>
          </UInput>
          <UInput
            v-model="filters.slugSearch"
            placeholder="Пошук за slug"
            class="w-[200px]"
          >
            <template #leading>
              <Hash class="w-5 h-5" />
            </template>
          </UInput>
          <USelect
            v-model="filters.status"
            :items="statusOptions"
            placeholder="Статус"
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
            Очистити фільтри
          </UButton>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <HasPermissions :required-permissions="['Create Product Brand']">
            <UButton @click="router.push('/products/brands/create')">
              <template #leading>
                <Plus class="w-4 h-4" />
              </template>
              Додати бренд
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
        :brands="filteredBrands"
        :loading="loading"
        @edit="handleEdit"
        @delete="handleDelete"
      />
    </HasPermissions>
  </VSidebarContent>
</template>

<script setup lang="ts">
import HasPermissions from "~/components/common/VHasPermissions.vue";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import ProductBrandsTable from "~/components/products/brands/tables/ProductBrandsTable.vue";
import VProductBrandDeleteModal from "~/components/products/brands/modals/VProductBrandDeleteModal.vue";
import type { ProductBrand } from "~/models/productBrand";
import { Plus, Search, Hash, X } from "lucide-vue-next";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Read Product Brands"],
});

const router = useRouter();
const productBrandStore = useProductBrandStore();

const isDeleteModalOpen = ref(false);
const brandToDelete = ref<ProductBrand | null>(null);

// Filters
const filters = ref({
  search: "",
  slugSearch: "",
  status: null as string | null,
});

const statusOptions = [
  { label: "Опубліковано", value: "published" },
  { label: "Чернетка", value: "draft" },
];

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
  data: brandsData,
  refresh: refreshBrandsData,
  status,
} = await productBrandStore.fetchProductBrands();

// Filter brands
const filteredBrands = computed(() => {
  if (!brandsData.value) return [];

  if (!hasActiveFilters.value) {
    return brandsData.value;
  }

  return brandsData.value.filter(brand => {
    const matchesSearch = !filters.value.search ||
      brand.name.toLowerCase().includes(filters.value.search.toLowerCase());

    const matchesSlug = !filters.value.slugSearch ||
      brand.slug.toLowerCase().includes(filters.value.slugSearch.toLowerCase());

    const matchesStatus = !filters.value.status ||
      brand.status === filters.value.status;

    return matchesSearch && matchesSlug && matchesStatus;
  });
});

const loading = computed(() => status.value === "pending");

const handleEdit = (brand: ProductBrand) => {
  router.push(`/products/brands/${brand.id}/edit`);
};

const handleDelete = (brand: ProductBrand) => {
  brandToDelete.value = brand;
  isDeleteModalOpen.value = true;
};

const refreshBrands = async () => {
  await refreshBrandsData();
};
</script>
