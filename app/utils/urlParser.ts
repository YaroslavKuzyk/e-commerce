/**
 * URL Parser for slug-based category routing
 * Parses URLs like: /category/computers/laptops/brand-apple-samsung/color-black/price-1000-5000
 */

export interface ParsedFilters {
  brandSlugs?: string[];
  attributeFilters?: Record<string, string[]>;
  priceMin?: number;
  priceMax?: number;
  inStock?: boolean;
  hasDiscount?: boolean;
  isClearance?: boolean;
  sortBy?: string;
}

export interface ParsedCategoryUrl {
  categoryPath: string[];
  filters: ParsedFilters;
}

// Reserved filter prefixes
const FILTER_PREFIXES = {
  brand: "brand-",
  price: "price-",
  inStock: "in-stock",
  hasDiscount: "akcii",
  isClearance: "ucinka",
  sort: "sort-",
} as const;

/**
 * Check if a segment is a filter segment (starts with known prefix or is a known attribute)
 */
export function isFilterSegment(
  segment: string,
  knownAttributeSlugs: string[]
): boolean {
  // Check reserved prefixes
  if (
    segment.startsWith(FILTER_PREFIXES.brand) ||
    segment.startsWith(FILTER_PREFIXES.price) ||
    segment === FILTER_PREFIXES.inStock ||
    segment === FILTER_PREFIXES.hasDiscount ||
    segment === FILTER_PREFIXES.isClearance ||
    segment.startsWith(FILTER_PREFIXES.sort)
  ) {
    return true;
  }

  // Check if it's an attribute filter (e.g., "color-black-white")
  const firstDash = segment.indexOf("-");
  if (firstDash > 0) {
    const potentialAttrSlug = segment.substring(0, firstDash);
    return knownAttributeSlugs.includes(potentialAttrSlug);
  }

  return false;
}

/**
 * Parse a filter segment and extract its values
 */
function parseFilterSegment(
  segment: string,
  knownAttributeSlugs: string[]
): { type: string; values: string[] } | null {
  // Brand filter: brand-apple-samsung
  if (segment.startsWith(FILTER_PREFIXES.brand)) {
    const values = segment.substring(FILTER_PREFIXES.brand.length).split("-");
    return { type: "brand", values };
  }

  // Price filter: price-1000-5000
  if (segment.startsWith(FILTER_PREFIXES.price)) {
    const values = segment.substring(FILTER_PREFIXES.price.length).split("-");
    return { type: "price", values };
  }

  // In stock: in-stock
  if (segment === FILTER_PREFIXES.inStock) {
    return { type: "inStock", values: ["true"] };
  }

  // Has discount: akcii
  if (segment === FILTER_PREFIXES.hasDiscount) {
    return { type: "hasDiscount", values: ["true"] };
  }

  // Is clearance: ucinka
  if (segment === FILTER_PREFIXES.isClearance) {
    return { type: "isClearance", values: ["true"] };
  }

  // Sort: sort-price-asc or sort-name
  if (segment.startsWith(FILTER_PREFIXES.sort)) {
    const value = segment.substring(FILTER_PREFIXES.sort.length);
    return { type: "sort", values: [value] };
  }

  // Attribute filter: color-black-white
  const firstDash = segment.indexOf("-");
  if (firstDash > 0) {
    const attrSlug = segment.substring(0, firstDash);
    if (knownAttributeSlugs.includes(attrSlug)) {
      const values = segment.substring(firstDash + 1).split("-");
      return { type: `attr:${attrSlug}`, values };
    }
  }

  return null;
}

/**
 * Parse a category URL with potential filter segments
 * @param slugArray - Array of URL segments (from [...slug] route param)
 * @param knownAttributeSlugs - Array of known attribute slugs (from API)
 */
export function parseCategoryUrl(
  slugArray: string[],
  knownAttributeSlugs: string[] = []
): ParsedCategoryUrl {
  const categoryPath: string[] = [];
  const filters: ParsedFilters = {};

  let foundFilter = false;

  for (const segment of slugArray) {
    if (!foundFilter && !isFilterSegment(segment, knownAttributeSlugs)) {
      // This is a category slug
      categoryPath.push(segment);
    } else {
      // This is a filter segment
      foundFilter = true;
      const parsed = parseFilterSegment(segment, knownAttributeSlugs);

      if (parsed) {
        switch (parsed.type) {
          case "brand":
            filters.brandSlugs = parsed.values;
            break;
          case "price":
            if (parsed.values.length >= 1 && parsed.values[0]) {
              filters.priceMin = parseInt(parsed.values[0], 10);
            }
            if (parsed.values.length >= 2 && parsed.values[1]) {
              filters.priceMax = parseInt(parsed.values[1], 10);
            }
            break;
          case "inStock":
            filters.inStock = true;
            break;
          case "hasDiscount":
            filters.hasDiscount = true;
            break;
          case "isClearance":
            filters.isClearance = true;
            break;
          case "sort":
            filters.sortBy = parsed.values[0];
            break;
          default:
            // Attribute filter
            if (parsed.type.startsWith("attr:")) {
              const attrSlug = parsed.type.substring(5);
              if (!filters.attributeFilters) {
                filters.attributeFilters = {};
              }
              filters.attributeFilters[attrSlug] = parsed.values;
            }
        }
      }
    }
  }

  return { categoryPath, filters };
}

/**
 * Parse product URL
 * Format: /product/{category-path}/{product-slug} or /product/{category-path}/{product-slug}/{variant-slug}
 *
 * The URL structure is:
 * - /product/product-slug (no category)
 * - /product/category/product-slug (single category)
 * - /product/parent-category/child-category/product-slug (nested categories)
 * - /product/category/product-slug/variant-slug (with variant)
 *
 * Since we can't distinguish between a category slug and a variant slug without API calls,
 * this function returns the last segment as the product slug.
 * Use parseProductUrlWithVariant when you need to check for variant slugs.
 */
export function parseProductUrl(slugArray: string[]): {
  categorySlug: string | null;
  productSlug: string | null;
  variantSlug: string | null;
} {
  if (slugArray.length === 0) {
    return { categorySlug: null, productSlug: null, variantSlug: null };
  }

  // The last segment is the product slug (or potentially variant slug)
  // We'll treat the last segment as product slug initially
  // After fetching, if the product has variants, we can check if there's a variant slug

  if (slugArray.length === 1) {
    return { categorySlug: null, productSlug: slugArray[0], variantSlug: null };
  }

  // For 2+ segments, last is product, second-to-last is the deepest category
  const productSlug = slugArray[slugArray.length - 1] || null;
  const categoryPath = slugArray.slice(0, -1);
  const categorySlug = categoryPath.length > 0 ? (categoryPath[categoryPath.length - 1] ?? null) : null;

  return { categorySlug, productSlug, variantSlug: null };
}

/**
 * Parse product URL with potential variant slug
 * This is used after initial product fetch to determine if the URL contains a variant
 */
export function parseProductUrlWithVariant(
  slugArray: string[],
  productSlugFromApi: string
): {
  categoryPath: string[];
  productSlug: string;
  variantSlug: string | null;
} {
  if (slugArray.length === 0) {
    return { categoryPath: [], productSlug: productSlugFromApi, variantSlug: null };
  }

  // Find the position of the product slug in the array
  const productIndex = slugArray.findIndex(seg => seg === productSlugFromApi);

  if (productIndex === -1) {
    // Product slug not found in URL, last segment might be variant
    // Assume second-to-last is product slug
    if (slugArray.length >= 2) {
      return {
        categoryPath: slugArray.slice(0, -2),
        productSlug: slugArray[slugArray.length - 2] ?? productSlugFromApi,
        variantSlug: slugArray[slugArray.length - 1] ?? null
      };
    }
    return {
      categoryPath: [],
      productSlug: slugArray[slugArray.length - 1] ?? productSlugFromApi,
      variantSlug: null
    };
  }

  // Product slug found, everything after it is variant slug
  const categoryPath = slugArray.slice(0, productIndex);
  const variantSlug = productIndex < slugArray.length - 1 ? (slugArray[productIndex + 1] ?? null) : null;

  return { categoryPath, productSlug: productSlugFromApi, variantSlug };
}
