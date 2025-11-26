import { defineStore } from 'pinia';
import type { ProductFormData, ProductFilters, ProductVariantFormData, ProductSpecificationFormData } from '~/models/product';

export const useProductStore = defineStore('product', () => {
  const {
    getAllProducts,
    getAllProductsPromise,
    getProductById,
    createProduct,
    updateProduct,
    deleteProduct,
    generateSlug,
    syncAttributes,
    getVariants,
    addVariant,
    updateVariant,
    deleteVariant,
    getSpecifications,
    addSpecification,
    updateSpecification,
    deleteSpecification,
    reorderSpecifications,
  } = useProduct();

  return {
    fetchProducts: async (filters?: ProductFilters) => await getAllProducts(filters),
    fetchProductsPromise: (filters?: ProductFilters) => getAllProductsPromise(filters),
    fetchProductById: async (id: number) => await getProductById(id),
    onCreateProduct: async (payload: ProductFormData) => await createProduct(payload),
    onUpdateProduct: async (id: number, payload: Partial<ProductFormData>) => await updateProduct(id, payload),
    onDeleteProduct: async (id: number) => await deleteProduct(id),
    onGenerateSlug: async (name: string) => await generateSlug(name),
    onSyncAttributes: async (productId: number, attributeIds: number[]) => await syncAttributes(productId, attributeIds),
    fetchVariants: async (productId: number) => await getVariants(productId),
    onAddVariant: async (productId: number, payload: ProductVariantFormData) => await addVariant(productId, payload),
    onUpdateVariant: async (productId: number, variantId: number, payload: Partial<ProductVariantFormData>) => await updateVariant(productId, variantId, payload),
    onDeleteVariant: async (productId: number, variantId: number) => await deleteVariant(productId, variantId),
    fetchSpecifications: async (productId: number) => await getSpecifications(productId),
    onAddSpecification: async (productId: number, payload: ProductSpecificationFormData) => await addSpecification(productId, payload),
    onUpdateSpecification: async (productId: number, specificationId: number, payload: Partial<ProductSpecificationFormData>) => await updateSpecification(productId, specificationId, payload),
    onDeleteSpecification: async (productId: number, specificationId: number) => await deleteSpecification(productId, specificationId),
    onReorderSpecifications: async (productId: number, specificationIds: number[]) => await reorderSpecifications(productId, specificationIds),
  };
});
