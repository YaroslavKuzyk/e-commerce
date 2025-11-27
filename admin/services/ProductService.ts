import type {
  Product,
  ProductFormData,
  ProductFilters,
  ProductPaginatedResponse,
  ProductVariant,
  ProductVariantFormData,
  ProductVariantFilters,
  ProductSpecification,
  ProductSpecificationFormData,
  IProductProvider,
} from '~/models/product';

export class ProductService implements IProductProvider {
  getAllProducts(filters?: ProductFilters) {
    const client = useSanctumClient();

    const params = new URLSearchParams();
    if (filters?.name) params.append('name', filters.name);
    if (filters?.slug) params.append('slug', filters.slug);
    if (filters?.status) params.append('status', filters.status);
    if (filters?.category_id) params.append('category_id', filters.category_id.toString());
    if (filters?.brand_id) params.append('brand_id', filters.brand_id.toString());
    if (filters?.page) params.append('page', filters.page.toString());
    if (filters?.per_page) params.append('per_page', filters.per_page.toString());

    const queryString = params.toString();
    const url = `/api/admin/products${queryString ? `?${queryString}` : ''}`;

    return useAsyncData<ProductPaginatedResponse>(
      'products',
      async (): Promise<ProductPaginatedResponse> => {
        const res = await client<{ success: boolean; data: Product[]; meta?: ProductPaginatedResponse['meta'] }>(url);
        return {
          data: res.data,
          meta: res.meta,
        };
      }
    );
  }

  async getAllProductsPromise(filters?: ProductFilters): Promise<ProductPaginatedResponse> {
    const client = useSanctumClient();

    const params = new URLSearchParams();
    if (filters?.name) params.append('name', filters.name);
    if (filters?.slug) params.append('slug', filters.slug);
    if (filters?.status) params.append('status', filters.status);
    if (filters?.category_id) params.append('category_id', filters.category_id.toString());
    if (filters?.brand_id) params.append('brand_id', filters.brand_id.toString());
    if (filters?.page) params.append('page', filters.page.toString());
    if (filters?.per_page) params.append('per_page', filters.per_page.toString());

    const queryString = params.toString();
    const url = `/api/admin/products${queryString ? `?${queryString}` : ''}`;

    const res = await client<{ success: boolean; data: Product[]; meta?: ProductPaginatedResponse['meta'] }>(url);
    return {
      data: res.data,
      meta: res.meta,
    };
  }

  getProductById(id: number) {
    const client = useSanctumClient();

    return useAsyncData<Product>(
      `product-${id}`,
      () =>
        client<{ success: boolean; data: Product }>(
          `/api/admin/products/${id}`
        ).then((res) => res.data)
    );
  }

  createProduct(payload: ProductFormData) {
    const client = useSanctumClient();

    return useAsyncData<Product>(
      `product-create-${Date.now()}`,
      () =>
        client<{ success: boolean; data: Product }>(
          '/api/admin/products',
          {
            method: 'POST',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  updateProduct(id: number, payload: Partial<ProductFormData>) {
    const client = useSanctumClient();

    return useAsyncData<Product>(
      `product-update-${id}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: Product }>(
          `/api/admin/products/${id}`,
          {
            method: 'PUT',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  deleteProduct(id: number) {
    const client = useSanctumClient();

    return useAsyncData<{ success: boolean; message: string }>(
      `product-delete-${id}-${Date.now()}`,
      () =>
        client<{ success: boolean; message: string }>(
          `/api/admin/products/${id}`,
          {
            method: 'DELETE',
          }
        ).then((res) => res)
    );
  }

  generateSlug(name: string) {
    const client = useSanctumClient();

    return useAsyncData<{ slug: string }>(
      `product-slug-${Date.now()}`,
      () =>
        client<{ success: boolean; data: { slug: string } }>(
          '/api/admin/products/generate-slug',
          {
            method: 'POST',
            body: { name },
          }
        ).then((res) => res.data)
    );
  }

  syncAttributes(productId: number, attributeIds: number[]) {
    const client = useSanctumClient();

    return useAsyncData<Product>(
      `product-sync-attrs-${productId}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: Product }>(
          `/api/admin/products/${productId}/attributes`,
          {
            method: 'POST',
            body: { attribute_ids: attributeIds },
          }
        ).then((res) => res.data)
    );
  }

  getVariants(productId: number, filters?: ProductVariantFilters) {
    const client = useSanctumClient();

    const params = new URLSearchParams();
    if (filters?.search) params.append('search', filters.search);
    if (filters?.slug) params.append('slug', filters.slug);
    if (filters?.status) params.append('status', filters.status);

    const queryString = params.toString();
    const url = `/api/admin/products/${productId}/variants${queryString ? `?${queryString}` : ''}`;

    return useAsyncData<ProductVariant[]>(
      `product-variants-${productId}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: ProductVariant[] }>(url).then((res) => res.data)
    );
  }

  async getVariantsPromise(productId: number, filters?: ProductVariantFilters): Promise<ProductVariant[]> {
    const client = useSanctumClient();

    const params = new URLSearchParams();
    if (filters?.search) params.append('search', filters.search);
    if (filters?.slug) params.append('slug', filters.slug);
    if (filters?.status) params.append('status', filters.status);

    const queryString = params.toString();
    const url = `/api/admin/products/${productId}/variants${queryString ? `?${queryString}` : ''}`;

    const res = await client<{ success: boolean; data: ProductVariant[] }>(url);
    return res.data;
  }

  addVariant(productId: number, payload: ProductVariantFormData) {
    const client = useSanctumClient();

    return useAsyncData<ProductVariant>(
      `product-add-variant-${productId}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: ProductVariant }>(
          `/api/admin/products/${productId}/variants`,
          {
            method: 'POST',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  updateVariant(productId: number, variantId: number, payload: Partial<ProductVariantFormData>) {
    const client = useSanctumClient();

    return useAsyncData<ProductVariant>(
      `product-update-variant-${productId}-${variantId}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: ProductVariant }>(
          `/api/admin/products/${productId}/variants/${variantId}`,
          {
            method: 'PUT',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  deleteVariant(productId: number, variantId: number) {
    const client = useSanctumClient();

    return useAsyncData<{ success: boolean; message: string }>(
      `product-delete-variant-${productId}-${variantId}-${Date.now()}`,
      () =>
        client<{ success: boolean; message: string }>(
          `/api/admin/products/${productId}/variants/${variantId}`,
          {
            method: 'DELETE',
          }
        ).then((res) => res)
    );
  }

  getSpecifications(productId: number) {
    const client = useSanctumClient();

    return useAsyncData<ProductSpecification[]>(
      `product-specifications-${productId}`,
      () =>
        client<{ success: boolean; data: ProductSpecification[] }>(
          `/api/admin/products/${productId}/specifications`
        ).then((res) => res.data)
    );
  }

  addSpecification(productId: number, payload: ProductSpecificationFormData) {
    const client = useSanctumClient();

    return useAsyncData<ProductSpecification>(
      `product-add-spec-${productId}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: ProductSpecification }>(
          `/api/admin/products/${productId}/specifications`,
          {
            method: 'POST',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  updateSpecification(productId: number, specificationId: number, payload: Partial<ProductSpecificationFormData>) {
    const client = useSanctumClient();

    return useAsyncData<ProductSpecification>(
      `product-update-spec-${productId}-${specificationId}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: ProductSpecification }>(
          `/api/admin/products/${productId}/specifications/${specificationId}`,
          {
            method: 'PUT',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  deleteSpecification(productId: number, specificationId: number) {
    const client = useSanctumClient();

    return useAsyncData<{ success: boolean; message: string }>(
      `product-delete-spec-${productId}-${specificationId}-${Date.now()}`,
      () =>
        client<{ success: boolean; message: string }>(
          `/api/admin/products/${productId}/specifications/${specificationId}`,
          {
            method: 'DELETE',
          }
        ).then((res) => res)
    );
  }

  reorderSpecifications(productId: number, specificationIds: number[]) {
    const client = useSanctumClient();

    return useAsyncData<ProductSpecification[]>(
      `product-reorder-specs-${productId}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: ProductSpecification[] }>(
          `/api/admin/products/${productId}/specifications/reorder`,
          {
            method: 'POST',
            body: { specification_ids: specificationIds },
          }
        ).then((res) => res.data)
    );
  }
}
