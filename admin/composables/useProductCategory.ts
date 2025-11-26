import { ProductCategoryService } from '~/services/ProductCategoryService';

export const useProductCategory = () => {
  const provider = new ProductCategoryService();

  return {
    getAllProductCategories: () => provider.getAllProductCategories(),
    getProductCategoryById: (id: number) => provider.getProductCategoryById(id),
    createProductCategory: (payload: any) => provider.createProductCategory(payload),
    updateProductCategory: (id: number, payload: any) => provider.updateProductCategory(id, payload),
    deleteProductCategory: (id: number) => provider.deleteProductCategory(id),
    generateSlug: (name: string) => provider.generateSlug(name),
  };
};
