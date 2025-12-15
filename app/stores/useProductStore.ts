import { defineStore } from "pinia";
import type { ProductFilters } from "~/models/product";

export const useProductStore = defineStore("product", () => {
  const { getProductsPaginated, getLatestProducts, getProductById, getProductBySlug } =
    useProduct();

  return {
    fetchProductsPaginated: async (page?: number, limit?: number, filters?: ProductFilters) =>
      await getProductsPaginated(page, limit, filters),
    fetchLatestProducts: async (limit?: number) =>
      await getLatestProducts(limit),
    fetchProductById: async (id: number) =>
      await getProductById(id),
    fetchProductBySlug: async (slug: string) =>
      await getProductBySlug(slug),
  };
});
