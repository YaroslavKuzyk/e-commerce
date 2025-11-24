import { defineStore } from 'pinia';
import type { ProductCategory, ProductCategoryFormData } from '~/models/productCategory';

export const useProductCategoryStore = defineStore('productCategory', () => {
  const {
    getAllProductCategories,
    getProductCategoryById,
    createProductCategory,
    updateProductCategory,
    deleteProductCategory,
    generateSlug,
  } = useProductCategory();

  return {
    fetchProductCategories: async () => await getAllProductCategories(),
    fetchProductCategoryById: async (id: number) => await getProductCategoryById(id),
    onCreateProductCategory: async (payload: ProductCategoryFormData) => await createProductCategory(payload),
    onUpdateProductCategory: async (id: number, payload: ProductCategoryFormData) => await updateProductCategory(id, payload),
    onDeleteProductCategory: async (id: number) => await deleteProductCategory(id),
    onGenerateSlug: async (name: string) => await generateSlug(name),
  };
});
