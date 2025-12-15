import type {
  Product,
  ProductFilters,
  IProductProvider,
} from "~/models/product";
import type { PaginatedResponse } from "~/models/common";

export class ProductService implements IProductProvider {
  getProductsPaginated(page: number = 1, limit: number = 15, filters: ProductFilters = {}) {
    const client = useSanctumClient();

    const params = new URLSearchParams();
    params.append("page", page.toString());
    params.append("limit", limit.toString());

    if (filters.category_id) {
      params.append("category_id", filters.category_id.toString());
    }
    if (filters.brand_id) {
      params.append("brand_id", filters.brand_id.toString());
    }

    return useAsyncData<Product[]>(
      `products-page-${page}-limit-${limit}-${JSON.stringify(filters)}`,
      () =>
        client<{ success: boolean } & PaginatedResponse<Product>>(
          `/api/products?${params.toString()}`
        ).then((res) => res.data),
      { watch: false }
    );
  }

  getLatestProducts(limit: number = 8) {
    const client = useSanctumClient();

    return useAsyncData<Product[]>("latest-products", () =>
      client<{ success: boolean } & PaginatedResponse<Product>>(
        `/api/products?page=1&limit=${limit}`
      ).then((res) => res.data)
    );
  }

  getProductById(id: number) {
    const client = useSanctumClient();

    return useAsyncData<Product>(`product-${id}`, () =>
      client<{ success: boolean; data: Product }>(
        `/api/products/${id}`
      ).then((res) => res.data)
    );
  }

  getProductBySlug(slug: string) {
    const client = useSanctumClient();

    return useAsyncData<Product>(`product-slug-${slug}`, () =>
      client<{ success: boolean; data: Product }>(
        `/api/products/slug/${slug}`
      ).then((res) => res.data)
    );
  }
}
