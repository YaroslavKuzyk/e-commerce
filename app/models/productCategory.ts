export interface ProductCategory {
  id: number;
  parent_id: number | null;
  name: string;
  subtitle: string | null;
  slug: string;
  status: string;
  body_description: string | null;
  logo_file_id: number | null;
  menu_image_file_id: number | null;
  subcategories_count?: number;
  subcategories?: ProductCategory[];
  parent?: ProductCategory;
  created_at: string;
  updated_at: string;
}

export interface ProductCategoryTreeNode extends ProductCategory {
  children?: ProductCategoryTreeNode[];
  expanded?: boolean;
}

export interface IProductCategoryProvider {
  getAllProductCategories: () => ReturnType<
    typeof useAsyncData<ProductCategory[] | undefined>
  >;
  getProductCategoryById: (
    id: number
  ) => ReturnType<typeof useAsyncData<ProductCategory | undefined>>;
  getLatestCategories: (
    limit?: number
  ) => ReturnType<typeof useAsyncData<ProductCategory[] | undefined>>;
}
