import type { Attribute, AttributeValue } from './attribute';
import type { ProductCategory } from './productCategory';
import type { ProductBrand } from './productBrand';
import type { IFile } from './files';

export type ProductStatus = 'draft' | 'published';
export type ProductVariantStatus = 'draft' | 'published';

export interface ProductVariantImage {
  id: number;
  product_variant_id: number;
  file_id: number;
  file?: IFile;
  sort_order: number;
  is_primary: boolean;
  created_at: string;
  updated_at: string;
}

export interface ProductVariant {
  id: number;
  product_id: number;
  sku: string;
  slug: string;
  name: string | null;
  price: number;
  stock: number;
  status: ProductVariantStatus;
  is_default: boolean;
  attribute_values: AttributeValue[];
  images: ProductVariantImage[];
  created_at: string;
  updated_at: string;
}

export interface ProductSpecification {
  id: number;
  product_id: number;
  name: string;
  value: string;
  sort_order: number;
  created_at: string;
  updated_at: string;
}

export interface ProductSpecificationFormData {
  name: string;
  value: string;
  sort_order?: number;
}

export interface Product {
  id: number;
  name: string;
  slug: string;
  short_description: string | null;
  description: string | null;
  category_id: number | null;
  category?: ProductCategory;
  brand_id: number | null;
  brand?: ProductBrand;
  status: ProductStatus;
  base_price: number;
  main_image_file_id: number | null;
  main_image?: IFile;
  attributes: Attribute[];
  variants: ProductVariant[];
  specifications: ProductSpecification[];
  created_at: string;
  updated_at: string;
}

export interface ProductFormData {
  name: string;
  slug: string;
  short_description?: string | null;
  description?: string | null;
  category_id?: number | null;
  brand_id?: number | null;
  status: ProductStatus;
  base_price?: number;
  main_image_file_id?: number | null;
  attribute_ids?: number[];
  variants?: ProductVariantFormData[];
}

export interface ProductVariantFormData {
  sku: string;
  slug: string;
  name?: string | null;
  price: number;
  stock?: number;
  status?: ProductVariantStatus;
  is_default?: boolean;
  attribute_value_ids?: number[];
  images?: ProductVariantImageFormData[];
}

export interface ProductVariantImageFormData {
  file_id: number;
  sort_order?: number;
  is_primary?: boolean;
}

export interface ProductFilters {
  name?: string;
  slug?: string;
  status?: ProductStatus | null;
  category_id?: number | null;
  brand_id?: number | null;
  page?: number;
  per_page?: number;
}

export interface ProductPaginatedResponse {
  data: Product[];
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface IProductProvider {
  getAllProducts: (filters?: ProductFilters) => ReturnType<typeof useAsyncData<ProductPaginatedResponse | undefined>>;
  getProductById: (id: number) => ReturnType<typeof useAsyncData<Product>>;
  createProduct: (payload: ProductFormData) => ReturnType<typeof useAsyncData<Product>>;
  updateProduct: (id: number, payload: Partial<ProductFormData>) => ReturnType<typeof useAsyncData<Product>>;
  deleteProduct: (id: number) => ReturnType<typeof useAsyncData<{ success: boolean; message: string }>>;
  generateSlug: (name: string) => ReturnType<typeof useAsyncData<{ slug: string }>>;
  syncAttributes: (productId: number, attributeIds: number[]) => ReturnType<typeof useAsyncData<Product>>;
  getVariants: (productId: number) => ReturnType<typeof useAsyncData<ProductVariant[]>>;
  addVariant: (productId: number, payload: ProductVariantFormData) => ReturnType<typeof useAsyncData<ProductVariant>>;
  updateVariant: (productId: number, variantId: number, payload: Partial<ProductVariantFormData>) => ReturnType<typeof useAsyncData<ProductVariant>>;
  deleteVariant: (productId: number, variantId: number) => ReturnType<typeof useAsyncData<{ success: boolean; message: string }>>;
  getSpecifications: (productId: number) => ReturnType<typeof useAsyncData<ProductSpecification[]>>;
  addSpecification: (productId: number, payload: ProductSpecificationFormData) => ReturnType<typeof useAsyncData<ProductSpecification>>;
  updateSpecification: (productId: number, specificationId: number, payload: Partial<ProductSpecificationFormData>) => ReturnType<typeof useAsyncData<ProductSpecification>>;
  deleteSpecification: (productId: number, specificationId: number) => ReturnType<typeof useAsyncData<{ success: boolean; message: string }>>;
  reorderSpecifications: (productId: number, specificationIds: number[]) => ReturnType<typeof useAsyncData<ProductSpecification[]>>;
}
