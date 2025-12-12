import type {
  ProductCategory,
  IProductCategoryProvider,
} from "~/models/productCategory";

export class ProductCategoryService implements IProductCategoryProvider {
  getAllProductCategories() {
    const client = useSanctumClient();

    return useAsyncData<ProductCategory[]>("product-categories", () =>
      client<{ success: boolean; data: ProductCategory[] }>(
        "/api/admin/product-categories"
      ).then((res) => res.data)
    );
  }

  getProductCategoryById(id: number) {
    const client = useSanctumClient();

    return useAsyncData<ProductCategory>(`product-category-${id}`, () =>
      client<{ success: boolean; data: ProductCategory }>(
        `/api/admin/product-categories/${id}`
      ).then((res) => res.data)
    );
  }
}
