import { ProductService } from '~/services/ProductService';
import type { ProductFilters, ProductFormData, ProductVariantFormData, ProductSpecificationFormData } from '~/models/product';

export const useProduct = () => {
  const provider = new ProductService();

  return {
    getAllProducts: (filters?: ProductFilters) => provider.getAllProducts(filters),
    getAllProductsPromise: (filters?: ProductFilters) => provider.getAllProductsPromise(filters),
    getProductById: (id: number) => provider.getProductById(id),
    createProduct: (payload: ProductFormData) => provider.createProduct(payload),
    updateProduct: (id: number, payload: Partial<ProductFormData>) => provider.updateProduct(id, payload),
    deleteProduct: (id: number) => provider.deleteProduct(id),
    generateSlug: (name: string) => provider.generateSlug(name),
    syncAttributes: (productId: number, attributeIds: number[]) => provider.syncAttributes(productId, attributeIds),
    getVariants: (productId: number) => provider.getVariants(productId),
    addVariant: (productId: number, payload: ProductVariantFormData) => provider.addVariant(productId, payload),
    updateVariant: (productId: number, variantId: number, payload: Partial<ProductVariantFormData>) => provider.updateVariant(productId, variantId, payload),
    deleteVariant: (productId: number, variantId: number) => provider.deleteVariant(productId, variantId),
    getSpecifications: (productId: number) => provider.getSpecifications(productId),
    addSpecification: (productId: number, payload: ProductSpecificationFormData) => provider.addSpecification(productId, payload),
    updateSpecification: (productId: number, specificationId: number, payload: Partial<ProductSpecificationFormData>) => provider.updateSpecification(productId, specificationId, payload),
    deleteSpecification: (productId: number, specificationId: number) => provider.deleteSpecification(productId, specificationId),
    reorderSpecifications: (productId: number, specificationIds: number[]) => provider.reorderSpecifications(productId, specificationIds),
  };
};
