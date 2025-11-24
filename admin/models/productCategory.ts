export type ProductCategoryStatus = 'draft' | 'published';

export interface ProductCategory {
  id: number;
  parent_id: number | null;
  name: string;
  slug: string;
  status: ProductCategoryStatus;
  body_description: string | null;
  logo_file_id: number | null;
  menu_image_file_id: number | null;
  subcategories_count?: number;
  subcategories?: ProductCategory[];
  parent?: ProductCategory;
  created_at: string;
  updated_at: string;
}

export interface ProductCategoryFormData {
  parent_id?: number | null;
  name: string;
  slug: string;
  status: ProductCategoryStatus;
  body_description?: string | null;
  logo_file_id?: number | null;
  menu_image_file_id?: number | null;
}

export interface ProductCategoryTreeNode extends ProductCategory {
  children?: ProductCategoryTreeNode[];
  expanded?: boolean;
}
