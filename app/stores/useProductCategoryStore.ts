import { defineStore } from "pinia";

export const useProductCategoryStore = defineStore("productCategory", () => {
  const { getAllProductCategories, getProductCategoryById } =
    useProductCategory();

  return {
    fetchProductCategories: async () => await getAllProductCategories(),
    fetchProductCategoryById: async (id: number) =>
      await getProductCategoryById(id),
  };
});
