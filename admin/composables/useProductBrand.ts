import { ProductBrandService } from '~/services/ProductBrandService';
import type { ProductBrandFilters } from '~/models/productBrand';

export const useProductBrand = () => {
  const provider = new ProductBrandService();

  return {
    getAllProductBrands: (filters?: ProductBrandFilters) => provider.getAllProductBrands(filters),
    getAllProductBrandsPromise: (filters?: ProductBrandFilters) => provider.getAllProductBrandsPromise(filters),
    getProductBrandById: (id: number) => provider.getProductBrandById(id),
    createProductBrand: (payload: any) => provider.createProductBrand(payload),
    updateProductBrand: (id: number, payload: any) => provider.updateProductBrand(id, payload),
    deleteProductBrand: (id: number) => provider.deleteProductBrand(id),
    generateSlug: (name: string) => provider.generateSlug(name),
  };
};
