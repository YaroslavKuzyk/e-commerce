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
  slug: string;
  name: string | null;
  price: string;
  stock: number;
  is_default: boolean;
  status: string;
  override_pricing: boolean;
  discount_price: string | null;
  discount_percent: string | null;
  discount_starts_at: string | null;
  discount_ends_at: string | null;
  is_clearance: boolean;
  clearance_price: string | null;
  clearance_reason: string | null;
  current_price: number;
  discount_percentage: number | null;
  images?: ProductVariantImage[];
  attribute_values?: AttributeValue[];
  created_at: string;
  updated_at: string;
}

export interface AttributeValue {
  id: number;
  attribute_id: number;
  value: string;
  slug?: string;
  color_code?: string | null;
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
  discount_price: string | null;
  discount_percent: string | null;
  discount_starts_at: string | null;
  discount_ends_at: string | null;
  is_clearance: boolean;
  clearance_price: string | null;
  clearance_reason: string | null;
  current_price?: number;
  discount_percentage?: number | null;
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
  search?: string;
  category_id?: number;
  brand_id?: number;
  brand_ids?: number[];
  price_min?: number;
  price_max?: number;
  attribute_values?: number[];
  specifications?: Record<string, string>;
  in_stock?: boolean;
  has_discount?: boolean;
  is_clearance?: boolean;
  sort_by?: 'created_at' | 'name' | 'price_asc' | 'price_desc';
}

export interface PriceRange {
  min: number;
  max: number;
}

export interface FilterCategory {
  id: number;
  name: string;
  slug: string;
  products_count: number;
  logo_file_id?: number | null;
  subcategories?: FilterCategory[];
}

export interface FilterBrand {
  id: number;
  name: string;
  slug: string;
  products_count: number;
}

export interface FilterAttributeValue {
  id: number;
  value: string;
  slug: string;
  sort_order: number;
  variants_count: number;
}

export interface FilterAttribute {
  id: number;
  name: string;
  slug: string;
  type: string;
  values: FilterAttributeValue[];
}

export interface FilterSpecification {
  name: string;
  values: string[];
  products_count: number;
}

export interface AvailableFilters {
  price_range: PriceRange;
  categories: FilterCategory[];
  brands: FilterBrand[];
  attributes: FilterAttribute[];
  specifications: FilterSpecification[];
}
