import { defineStore } from 'pinia';
import type { ProductBrand, ProductBrandFormData } from '~/models/productBrand';

export const useProductBrandStore = defineStore('productBrand', () => {
  const {
    getAllProductBrands,
    getProductBrandById,
    createProductBrand,
    updateProductBrand,
    deleteProductBrand,
    generateSlug,
  } = useProductBrand();

  return {
    fetchProductBrands: async () => await getAllProductBrands(),
    fetchProductBrandById: async (id: number) => await getProductBrandById(id),
    onCreateProductBrand: async (payload: ProductBrandFormData) => await createProductBrand(payload),
    onUpdateProductBrand: async (id: number, payload: ProductBrandFormData) => await updateProductBrand(id, payload),
    onDeleteProductBrand: async (id: number) => await deleteProductBrand(id),
    onGenerateSlug: async (name: string) => await generateSlug(name),
  };
});
