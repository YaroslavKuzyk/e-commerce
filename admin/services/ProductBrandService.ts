import type {
  ProductBrand,
  ProductBrandFormData,
  IProductBrandProvider,
} from '~/models/productBrand';

export class ProductBrandService implements IProductBrandProvider {
  getAllProductBrands() {
    const client = useSanctumClient();

    return useAsyncData<ProductBrand[]>('product-brands', () =>
      client<{ success: boolean; data: ProductBrand[] }>(
        '/api/admin/product-brands'
      ).then((res) => res.data)
    );
  }

  getProductBrandById(id: number) {
    const client = useSanctumClient();

    return useAsyncData<ProductBrand>(
      `product-brand-${id}`,
      () =>
        client<{ success: boolean; data: ProductBrand }>(
          `/api/admin/product-brands/${id}`
        ).then((res) => res.data)
    );
  }

  createProductBrand(payload: ProductBrandFormData) {
    const client = useSanctumClient();

    return useAsyncData<ProductBrand>(
      `product-brand-create-${Date.now()}`,
      () =>
        client<{ success: boolean; data: ProductBrand }>(
          '/api/admin/product-brands',
          {
            method: 'POST',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  updateProductBrand(id: number, payload: ProductBrandFormData) {
    const client = useSanctumClient();

    return useAsyncData<ProductBrand>(
      `product-brand-update-${id}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: ProductBrand }>(
          `/api/admin/product-brands/${id}`,
          {
            method: 'PUT',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  deleteProductBrand(id: number) {
    const client = useSanctumClient();

    return useAsyncData<{ success: boolean; message: string }>(
      `product-brand-delete-${id}-${Date.now()}`,
      () =>
        client<{ success: boolean; message: string }>(
          `/api/admin/product-brands/${id}`,
          {
            method: 'DELETE',
          }
        ).then((res) => res)
    );
  }

  generateSlug(name: string) {
    const client = useSanctumClient();

    return useAsyncData<{ slug: string }>(
      `product-brand-slug-${Date.now()}`,
      () =>
        client<{ success: boolean; data: { slug: string } }>(
          '/api/admin/product-brands/generate-slug',
          {
            method: 'POST',
            body: { name },
          }
        ).then((res) => res.data)
    );
  }
}
