import type {
  Product,
  ProductFilters,
  AvailableFilters,
  IProductProvider,
} from "~/models/product";
import type { PaginatedResponse } from "~/models/common";

export class ProductService implements IProductProvider {
  getProductsPaginated(page: number = 1, limit: number = 15, filters: ProductFilters = {}) {
    const client = useSanctumClient();

    const params = new URLSearchParams();
    params.append("page", page.toString());
    params.append("limit", limit.toString());

    // Search
    if (filters.search) {
      params.append("search", filters.search);
    }

    // Category
    if (filters.category_id) {
      params.append("category_id", filters.category_id.toString());
    }

    // Single brand (backwards compatible)
    if (filters.brand_id) {
      params.append("brand_id", filters.brand_id.toString());
    }

    // Multiple brands
    if (filters.brand_ids && filters.brand_ids.length > 0) {
      params.append("brand_ids", filters.brand_ids.join(","));
    }

    // Price range
    if (filters.price_min !== undefined) {
      params.append("price_min", filters.price_min.toString());
    }
    if (filters.price_max !== undefined) {
      params.append("price_max", filters.price_max.toString());
    }

    // Attribute values
    if (filters.attribute_values && filters.attribute_values.length > 0) {
      params.append("attribute_values", filters.attribute_values.join(","));
    }

    // Specifications
    if (filters.specifications) {
      Object.entries(filters.specifications).forEach(([name, value]) => {
        params.append(`specs[${name}]`, value);
      });
    }

    // In stock
    if (filters.in_stock !== undefined) {
      params.append("in_stock", filters.in_stock.toString());
    }

    // Has discount (Акції)
    if (filters.has_discount !== undefined) {
      params.append("has_discount", filters.has_discount.toString());
    }

    // Is clearance (Уцінка)
    if (filters.is_clearance !== undefined) {
      params.append("is_clearance", filters.is_clearance.toString());
    }

    // Sorting
    if (filters.sort_by) {
      params.append("sort_by", filters.sort_by);
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

  getAvailableFilters(categoryId?: number) {
    const client = useSanctumClient();

    const params = new URLSearchParams();
    if (categoryId) {
      params.append("category_id", categoryId.toString());
    }

    return useAsyncData<AvailableFilters>(
      `product-filters-${categoryId || "all"}`,
      () =>
        client<{ success: boolean; data: AvailableFilters }>(
          `/api/products/filters?${params.toString()}`
        ).then((res) => res.data)
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
