import type { ProductCategory } from "./productCategory";

export interface ProductBrand {
  id: number;
  name: string;
  slug: string;
  status: string;
  description: string | null;
  logo_file_id: number | null;
  created_at: string;
  updated_at: string;
}

export interface ProductVariantImage {
  id: number;
  variant_id: number;
  file_id: number;
  sort_order: number;
  is_primary: boolean;
  file?: {
    id: number;
    filename: string;
    path: string;
  };
}

export interface ProductVariant {
  id: number;
  product_id: number;
  sku: string;
  price: string;
  stock_quantity: number;
  is_default: boolean;
  status: string;
  images?: ProductVariantImage[];
  attribute_values?: AttributeValue[];
  created_at: string;
  updated_at: string;
}

export interface AttributeValue {
  id: number;
  attribute_id: number;
  value: string;
  sort_order: number;
  attribute?: Attribute;
}

export interface Attribute {
  id: number;
  name: string;
  slug: string;
  type: string;
  values?: AttributeValue[];
}

export interface ProductSpecification {
  id: number;
  product_id: number;
  name: string;
  value: string;
  sort_order: number;
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
  status: string;
  base_price: string;
  main_image_file_id: number | null;
  main_image?: {
    id: number;
    filename: string;
    path: string;
  };
  attributes?: Attribute[];
  variants?: ProductVariant[];
  specifications?: ProductSpecification[];
  created_at: string;
  updated_at: string;
}

export interface IProductProvider {
  getProductsPaginated: (
    page?: number,
    limit?: number,
    filters?: ProductFilters
  ) => ReturnType<typeof useAsyncData<Product[] | undefined>>;
  getProductById: (
    id: number
  ) => ReturnType<typeof useAsyncData<Product | undefined>>;
  getProductBySlug: (
    slug: string
  ) => ReturnType<typeof useAsyncData<Product | undefined>>;
}

export interface ProductFilters {
  category_id?: number;
  brand_id?: number;
}
