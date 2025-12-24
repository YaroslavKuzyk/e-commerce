import { defineStore } from "pinia";

export const useProductCategoryStore = defineStore("productCategory", () => {
  const { getAllProductCategories, getProductCategoryById, getLatestCategories } =
    useProductCategory();

  return {
    fetchProductCategories: async () => await getAllProductCategories(),
    fetchProductCategoryById: async (id: number) =>
      await getProductCategoryById(id),
    fetchLatestCategories: async (limit?: number) =>
      await getLatestCategories(limit),
  };
});
