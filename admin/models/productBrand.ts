export type ProductBrandStatus = 'draft' | 'published';

export interface ProductBrand {
  id: number;
  name: string;
  slug: string;
  status: ProductBrandStatus;
  body_description: string | null;
  logo_file_id: number | null;
  menu_image_file_id: number | null;
  created_at: string;
  updated_at: string;
}

export interface ProductBrandFormData {
  name: string;
  slug: string;
  status: ProductBrandStatus;
  body_description?: string | null;
  logo_file_id?: number | null;
  menu_image_file_id?: number | null;
}

export interface ProductBrandFilters {
  name?: string;
  slug?: string;
  status?: ProductBrandStatus | null;
  page?: number;
  per_page?: number;
}

export interface ProductBrandPaginatedResponse {
  data: ProductBrand[];
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface IProductBrandProvider {
  getAllProductBrands: (filters?: ProductBrandFilters) => ReturnType<typeof useAsyncData<ProductBrandPaginatedResponse | undefined>>;
  getProductBrandById: (id: number) => ReturnType<typeof useAsyncData<ProductBrand>>;
  createProductBrand: (payload: ProductBrandFormData) => ReturnType<typeof useAsyncData<ProductBrand>>;
  updateProductBrand: (id: number, payload: ProductBrandFormData) => ReturnType<typeof useAsyncData<ProductBrand>>;
  deleteProductBrand: (id: number) => ReturnType<typeof useAsyncData<{ success: boolean; message: string }>>;
  generateSlug: (name: string) => ReturnType<typeof useAsyncData<{ slug: string }>>;
}
