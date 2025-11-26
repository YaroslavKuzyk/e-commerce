import { ProductBrandService } from '~/services/ProductBrandService';

export const useProductBrand = () => {
  const provider = new ProductBrandService();

  return {
    getAllProductBrands: () => provider.getAllProductBrands(),
    getProductBrandById: (id: number) => provider.getProductBrandById(id),
    createProductBrand: (payload: any) => provider.createProductBrand(payload),
    updateProductBrand: (id: number, payload: any) => provider.updateProductBrand(id, payload),
    deleteProductBrand: (id: number) => provider.deleteProductBrand(id),
    generateSlug: (name: string) => provider.generateSlug(name),
  };
};
