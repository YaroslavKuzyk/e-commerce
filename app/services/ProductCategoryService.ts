import type {
  ProductCategory,
  IProductCategoryProvider,
} from "~/models/productCategory";
import type { PaginatedResponse } from "~/models/common";

export class ProductCategoryService implements IProductCategoryProvider {
  getAllProductCategories() {
    const client = useSanctumClient();

    return useAsyncData<ProductCategory[]>("product-categories", () =>
      client<{ success: boolean; data: ProductCategory[] }>(
        "/api/product-categories"
      ).then((res) => res.data)
    );
  }

  getProductCategoryById(id: number) {
    const client = useSanctumClient();

    return useAsyncData<ProductCategory>(`product-category-${id}`, () =>
      client<{ success: boolean; data: ProductCategory }>(
        `/api/product-categories/${id}`
      ).then((res) => res.data)
    );
  }

  getLatestCategories(limit: number = 20) {
    const client = useSanctumClient();

    return useAsyncData<ProductCategory[]>("latest-categories", () =>
      client<{ success: boolean } & PaginatedResponse<ProductCategory>>(
        `/api/product-categories/flat?page=1&limit=${limit}`
      ).then((res) => res.data)
    );
  }
}
