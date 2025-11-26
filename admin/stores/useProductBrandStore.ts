import { defineStore } from 'pinia';
import type { ProductBrandFormData, ProductBrandFilters } from '~/models/productBrand';

export const useProductBrandStore = defineStore('productBrand', () => {
  const {
    getAllProductBrands,
    getAllProductBrandsPromise,
    getProductBrandById,
    createProductBrand,
    updateProductBrand,
    deleteProductBrand,
    generateSlug,
  } = useProductBrand();

  return {
    fetchProductBrands: async (filters?: ProductBrandFilters) => await getAllProductBrands(filters),
    fetchProductBrandsPromise: (filters?: ProductBrandFilters) => getAllProductBrandsPromise(filters),
    fetchProductBrandById: async (id: number) => await getProductBrandById(id),
    onCreateProductBrand: async (payload: ProductBrandFormData) => await createProductBrand(payload),
    onUpdateProductBrand: async (id: number, payload: ProductBrandFormData) => await updateProductBrand(id, payload),
    onDeleteProductBrand: async (id: number) => await deleteProductBrand(id),
    onGenerateSlug: async (name: string) => await generateSlug(name),
  };
});
