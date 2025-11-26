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

export interface IProductBrandProvider {
  getAllProductBrands: () => ReturnType<typeof useAsyncData<ProductBrand[]>>;
  getProductBrandById: (id: number) => ReturnType<typeof useAsyncData<ProductBrand>>;
  createProductBrand: (payload: ProductBrandFormData) => ReturnType<typeof useAsyncData<ProductBrand>>;
  updateProductBrand: (id: number, payload: ProductBrandFormData) => ReturnType<typeof useAsyncData<ProductBrand>>;
  deleteProductBrand: (id: number) => ReturnType<typeof useAsyncData<{ success: boolean; message: string }>>;
  generateSlug: (name: string) => ReturnType<typeof useAsyncData<{ slug: string }>>;
}
