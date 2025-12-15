import { ProductService } from "~/services/ProductService";
import type { ProductFilters } from "~/models/product";

export const useProduct = () => {
  const provider = new ProductService();

  return {
    getProductsPaginated: (page?: number, limit?: number, filters?: ProductFilters) =>
      provider.getProductsPaginated(page, limit, filters),
    getLatestProducts: (limit?: number) => provider.getLatestProducts(limit),
    getProductById: (id: number) => provider.getProductById(id),
    getProductBySlug: (slug: string) => provider.getProductBySlug(slug),
  };
};
