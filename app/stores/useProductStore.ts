import { defineStore } from "pinia";
import type { ProductFilters, AvailableFilters } from "~/models/product";

export const useProductStore = defineStore("product", () => {
  const { getProductsPaginated, getLatestProducts, getProductById, getProductBySlug, getAvailableFilters } =
    useProduct();

  // Store state for filters
  const availableFilters = ref<AvailableFilters | null>(null);
  const currentFilters = ref<ProductFilters>({});

  return {
    // State
    availableFilters,
    currentFilters,

    // Actions
    fetchProductsPaginated: async (page?: number, limit?: number, filters?: ProductFilters) =>
      await getProductsPaginated(page, limit, filters),
    fetchLatestProducts: async (limit?: number) =>
      await getLatestProducts(limit),
    fetchProductById: async (id: number) =>
      await getProductById(id),
    fetchProductBySlug: async (slug: string) =>
      await getProductBySlug(slug),
    fetchAvailableFilters: async (categoryId?: number) => {
      const result = await getAvailableFilters(categoryId);
      if (result.data.value) {
        availableFilters.value = result.data.value;
      }
      return result;
    },
    setCurrentFilters: (filters: ProductFilters) => {
      currentFilters.value = filters;
    },
    resetFilters: () => {
      currentFilters.value = {};
    },
  };
});
