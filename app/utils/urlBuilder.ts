/**
 * URL Builder for slug-based category routing
 * Builds URLs like: /category/computers/laptops/brand-apple-samsung/color-black/price-1000-5000
 */

import type { ParsedFilters } from "./urlParser";

export interface BuildCategoryUrlParams {
  categoryPath: string[];
  filters?: ParsedFilters;
}

/**
 * Build a category URL from category path and filters
 * Ensures consistent segment order for SEO
 */
export function buildCategoryUrl(params: BuildCategoryUrlParams): string {
  const { categoryPath, filters } = params;
  const segments: string[] = [...categoryPath];

  if (filters) {
    // 1. Brand filter (alphabetically sorted for consistency)
    if (filters.brandSlugs && filters.brandSlugs.length > 0) {
      const sortedBrands = [...filters.brandSlugs].sort();
      segments.push(`brand-${sortedBrands.join("-")}`);
    }

    // 2. Attribute filters (sorted by attribute slug, then by value)
    if (filters.attributeFilters) {
      const attrSlugs = Object.keys(filters.attributeFilters).sort();
      for (const attrSlug of attrSlugs) {
        const values = filters.attributeFilters[attrSlug];
        if (values && values.length > 0) {
          const sortedValues = [...values].sort();
          segments.push(`${attrSlug}-${sortedValues.join("-")}`);
        }
      }
    }

    // 3. Price filter
    if (
      filters.priceMin !== undefined ||
      filters.priceMax !== undefined
    ) {
      const min = filters.priceMin ?? "";
      const max = filters.priceMax ?? "";
      segments.push(`price-${min}-${max}`);
    }

    // 4. In stock filter
    if (filters.inStock) {
      segments.push("in-stock");
    }

    // 5. Has discount filter (Акції)
    if (filters.hasDiscount) {
      segments.push("akcii");
    }

    // 6. Is clearance filter (Уцінка)
    if (filters.isClearance) {
      segments.push("ucinka");
    }

    // 7. Sort filter
    if (filters.sortBy) {
      segments.push(`sort-${filters.sortBy}`);
    }
  }

  return `/category/${segments.join("/")}`;
}

/**
 * Build a product URL with optional variant slug
 * Format: /product/{category-path}/{product-slug} or /product/{category-path}/{product-slug}/{variant-slug}
 */
export function buildProductUrl(
  categoryPath: string[] | string | null,
  productSlug: string,
  variantSlug?: string | null
): string {
  let basePath: string;

  if (Array.isArray(categoryPath) && categoryPath.length > 0) {
    basePath = `/product/${categoryPath.join('/')}/${productSlug}`;
  } else if (typeof categoryPath === 'string' && categoryPath) {
    basePath = `/product/${categoryPath}/${productSlug}`;
  } else {
    basePath = `/product/${productSlug}`;
  }

  if (variantSlug) {
    return `${basePath}/${variantSlug}`;
  }

  return basePath;
}

/**
 * Convert ParsedFilters to API query params for by-slugs endpoint
 */
export function filtersToApiParams(
  categorySlug: string | null,
  filters: ParsedFilters,
  page: number = 1,
  limit: number = 12
): Record<string, string> {
  const params: Record<string, string> = {
    page: page.toString(),
    limit: limit.toString(),
  };

  if (categorySlug) {
    params.category_slug = categorySlug;
  }

  if (filters.brandSlugs && filters.brandSlugs.length > 0) {
    params.brand_slugs = filters.brandSlugs.join(",");
  }

  if (filters.attributeFilters) {
    for (const [attrSlug, values] of Object.entries(filters.attributeFilters)) {
      if (values && values.length > 0) {
        params[`attr_slugs[${attrSlug}]`] = values.join(",");
      }
    }
  }

  if (filters.priceMin !== undefined) {
    params.price_min = filters.priceMin.toString();
  }

  if (filters.priceMax !== undefined) {
    params.price_max = filters.priceMax.toString();
  }

  if (filters.inStock) {
    params.in_stock = "true";
  }

  if (filters.hasDiscount) {
    params.has_discount = "true";
  }

  if (filters.isClearance) {
    params.is_clearance = "true";
  }

  if (filters.sortBy) {
    params.sort_by = filters.sortBy;
  }

  return params;
}

/**
 * Merge current filters with new filter values
 */
export function updateFilters(
  current: ParsedFilters,
  updates: Partial<ParsedFilters>
): ParsedFilters {
  const result = { ...current };

  if (updates.brandSlugs !== undefined) {
    result.brandSlugs = updates.brandSlugs.length > 0 ? updates.brandSlugs : undefined;
  }

  if (updates.attributeFilters !== undefined) {
    result.attributeFilters = Object.keys(updates.attributeFilters).length > 0
      ? updates.attributeFilters
      : undefined;
  }

  if (updates.priceMin !== undefined) {
    result.priceMin = updates.priceMin || undefined;
  }

  if (updates.priceMax !== undefined) {
    result.priceMax = updates.priceMax || undefined;
  }

  if (updates.inStock !== undefined) {
    result.inStock = updates.inStock || undefined;
  }

  if (updates.hasDiscount !== undefined) {
    result.hasDiscount = updates.hasDiscount || undefined;
  }

  if (updates.isClearance !== undefined) {
    result.isClearance = updates.isClearance || undefined;
  }

  if (updates.sortBy !== undefined) {
    result.sortBy = updates.sortBy || undefined;
  }

  return result;
}

/**
 * Check if filters have any active values
 */
export function hasActiveFilters(filters: ParsedFilters): boolean {
  return !!(
    (filters.brandSlugs && filters.brandSlugs.length > 0) ||
    (filters.attributeFilters && Object.keys(filters.attributeFilters).length > 0) ||
    filters.priceMin !== undefined ||
    filters.priceMax !== undefined ||
    filters.inStock ||
    filters.hasDiscount ||
    filters.isClearance ||
    filters.sortBy
  );
}

/**
 * Clear all filters
 */
export function clearFilters(): ParsedFilters {
  return {};
}
