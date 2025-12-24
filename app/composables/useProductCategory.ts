import { ProductCategoryService } from "~/services/ProductCategoryService";

export const useProductCategory = () => {
  const provider = new ProductCategoryService();

  return {
    getAllProductCategories: () => provider.getAllProductCategories(),
    getProductCategoryById: (id: number) => provider.getProductCategoryById(id),
    getLatestCategories: (limit?: number) => provider.getLatestCategories(limit),
  };
};
