import type {
  ProductCategory,
  ProductCategoryFormData,
  IProductCategoryProvider,
} from '~/models/productCategory';

export class ProductCategoryService implements IProductCategoryProvider {
  getAllProductCategories() {
    const client = useSanctumClient();

    return useAsyncData<ProductCategory[]>('product-categories', () =>
      client<{ success: boolean; data: ProductCategory[] }>(
        '/api/admin/product-categories'
      ).then((res) => res.data)
    );
  }

  getProductCategoryById(id: number) {
    const client = useSanctumClient();

    return useAsyncData<ProductCategory>(
      `product-category-${id}`,
      () =>
        client<{ success: boolean; data: ProductCategory }>(
          `/api/admin/product-categories/${id}`
        ).then((res) => res.data)
    );
  }

  createProductCategory(payload: ProductCategoryFormData) {
    const client = useSanctumClient();

    return useAsyncData<ProductCategory>(
      `product-category-create-${Date.now()}`,
      () =>
        client<{ success: boolean; data: ProductCategory }>(
          '/api/admin/product-categories',
          {
            method: 'POST',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  updateProductCategory(id: number, payload: ProductCategoryFormData) {
    const client = useSanctumClient();

    return useAsyncData<ProductCategory>(
      `product-category-update-${id}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: ProductCategory }>(
          `/api/admin/product-categories/${id}`,
          {
            method: 'PUT',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  deleteProductCategory(id: number) {
    const client = useSanctumClient();

    return useAsyncData<{ success: boolean; message: string }>(
      `product-category-delete-${id}-${Date.now()}`,
      () =>
        client<{ success: boolean; message: string }>(
          `/api/admin/product-categories/${id}`,
          {
            method: 'DELETE',
          }
        ).then((res) => res)
    );
  }

  generateSlug(name: string) {
    const client = useSanctumClient();

    return useAsyncData<{ slug: string }>(
      `product-category-slug-${Date.now()}`,
      () =>
        client<{ success: boolean; data: { slug: string } }>(
          '/api/admin/product-categories/generate-slug',
          {
            method: 'POST',
            body: { name },
          }
        ).then((res) => res.data)
    );
  }
}
