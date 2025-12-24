import { parseCategoryUrl, type ParsedFilters } from "~/utils/urlParser";
import { buildCategoryUrl, hasActiveFilters, filtersToApiParams } from "~/utils/urlBuilder";
import type { Product, AvailableFilters, FilterCategory } from "~/models/product";

export interface AllProductsPageMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface BreadcrumbItem {
  label: string;
  to?: string;
}

export function useAllProductsPage() {
  const route = useRoute();
  const router = useRouter();
  const config = useRuntimeConfig();
  const apiBase = config.public.sanctum?.baseUrl || "http://localhost:8000";

  // State
  const attributeSlugs = ref<string[]>([]);
  const parentCategories = ref<FilterCategory[]>([]);
  const breadcrumbs = ref<BreadcrumbItem[]>([]);
  const products = ref<Product[]>([]);
  const meta = ref<AllProductsPageMeta | null>(null);
  const availableFilters = ref<AvailableFilters | null>(null);
  const parsedFilters = ref<ParsedFilters>({});
  const searchQuery = ref("");
  const selectedSort = ref<string | undefined>(undefined);
  const isLoading = ref(true);
  const currentPage = ref(1);
  const limit = 12;

  // Page title based on filters
  const pageTitle = computed(() => {
    if (parsedFilters.value.hasDiscount) return "Акції";
    if (parsedFilters.value.isClearance) return "Уцінка";
    if (parsedFilters.value.inStock) return "В наявності";
    return "Всі товари";
  });

  // Computed
  const slugArray = computed(() => {
    const slug = route.params.slug;
    if (Array.isArray(slug)) return slug;
    if (slug) return [slug];
    return [];
  });

  const hasFilters = computed(() =>
    !!searchQuery.value || hasActiveFilters(parsedFilters.value)
  );

  // API Functions
  const fetchAttributeSlugs = async () => {
    try {
      const response = await $fetch<{ success: boolean; data: string[] }>(
        `${apiBase}/api/products/attribute-slugs`
      );
      attributeSlugs.value = response.data;
    } catch (e) {
      console.error("Failed to fetch attribute slugs:", e);
    }
  };

  const fetchParentCategories = async () => {
    try {
      const response = await $fetch<{
        success: boolean;
        data: FilterCategory[];
      }>(`${apiBase}/api/product-categories/parents`);
      parentCategories.value = response.data;
    } catch (e) {
      console.error("Failed to fetch parent categories:", e);
    }
  };

  const fetchProducts = async () => {
    isLoading.value = true;

    try {
      const params = filtersToApiParams(
        null, // no category
        parsedFilters.value,
        currentPage.value,
        limit
      );

      if (searchQuery.value) {
        params.search = searchQuery.value;
      }

      const queryString = new URLSearchParams(params).toString();
      const response = await $fetch<{
        success: boolean;
        data: Product[];
        meta: AllProductsPageMeta;
      }>(`${apiBase}/api/products/variants?${queryString}`);

      products.value = response.data;
      meta.value = response.meta;
    } catch (e) {
      console.error("Failed to fetch products:", e);
      products.value = [];
      meta.value = null;
    } finally {
      isLoading.value = false;
    }
  };

  const fetchFilters = async () => {
    try {
      const response = await $fetch<{
        success: boolean;
        data: AvailableFilters;
      }>(`${apiBase}/api/products/filters-by-slug`);

      availableFilters.value = response.data;
    } catch (e) {
      console.error("Failed to fetch filters:", e);
    }
  };

  // Helper Functions
  const buildBreadcrumbs = (): BreadcrumbItem[] => {
    const items: BreadcrumbItem[] = [
      { label: "Головна", to: "/" },
    ];

    if (parsedFilters.value.hasDiscount) {
      items.push({ label: "Акції" });
    } else if (parsedFilters.value.isClearance) {
      items.push({ label: "Уцінка" });
    } else {
      items.push({ label: "Всі товари" });
    }

    return items;
  };

  const parseUrl = () => {
    const parsed = parseCategoryUrl(slugArray.value, attributeSlugs.value);
    parsedFilters.value = parsed.filters;
    breadcrumbs.value = buildBreadcrumbs();
  };

  const initializeData = async () => {
    await fetchAttributeSlugs();
    parseUrl();
    await Promise.all([fetchParentCategories(), fetchFilters()]);
    await fetchProducts();
  };

  // Navigation - builds URL for store page
  const buildAllProductsUrl = (filters: ParsedFilters): string => {
    const segments: string[] = [];

    // Brand filter
    if (filters.brandSlugs && filters.brandSlugs.length > 0) {
      const sortedBrands = [...filters.brandSlugs].sort();
      segments.push(`brand-${sortedBrands.join("-")}`);
    }

    // Attribute filters
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

    // Price filter
    if (filters.priceMin !== undefined || filters.priceMax !== undefined) {
      const min = filters.priceMin ?? "";
      const max = filters.priceMax ?? "";
      segments.push(`price-${min}-${max}`);
    }

    // In stock filter
    if (filters.inStock) {
      segments.push("in-stock");
    }

    // Has discount filter
    if (filters.hasDiscount) {
      segments.push("akcii");
    }

    // Is clearance filter
    if (filters.isClearance) {
      segments.push("ucinka");
    }

    // Sort filter
    if (filters.sortBy) {
      segments.push(`sort-${filters.sortBy}`);
    }

    if (segments.length > 0) {
      return `/store/${segments.join("/")}`;
    }
    return "/store";
  };

  const navigateWithFilters = (newFilters: ParsedFilters) => {
    const url = buildAllProductsUrl(newFilters);
    router.push(url);
  };

  // Filter Actions
  const onFiltersChange = (newFilters: ParsedFilters) => {
    currentPage.value = 1;
    navigateWithFilters(newFilters);
  };

  const onSortChange = () => {
    navigateWithFilters({ ...parsedFilters.value, sortBy: selectedSort.value });
    currentPage.value = 1;
  };

  const resetFilters = () => {
    searchQuery.value = "";
    selectedSort.value = undefined;
    currentPage.value = 1;
    navigateWithFilters({});
  };

  const removeSearch = () => {
    searchQuery.value = "";
    fetchProducts();
  };

  const removeBrand = (brandSlug: string) => {
    const newBrands = parsedFilters.value.brandSlugs?.filter(
      (b) => b !== brandSlug
    );
    navigateWithFilters({
      ...parsedFilters.value,
      brandSlugs: newBrands?.length ? newBrands : undefined,
    });
  };

  const removePriceRange = () => {
    const { priceMin, priceMax, ...rest } = parsedFilters.value;
    navigateWithFilters(rest);
  };

  const removeAttributeValue = (attrSlug: string, valueSlug: string) => {
    const newAttrFilters = { ...parsedFilters.value.attributeFilters };

    if (newAttrFilters[attrSlug]) {
      newAttrFilters[attrSlug] = newAttrFilters[attrSlug].filter(
        (v) => v !== valueSlug
      );
      if (newAttrFilters[attrSlug].length === 0) {
        delete newAttrFilters[attrSlug];
      }
    }

    navigateWithFilters({
      ...parsedFilters.value,
      attributeFilters: Object.keys(newAttrFilters).length
        ? newAttrFilters
        : undefined,
    });
  };

  const removeInStock = () => {
    navigateWithFilters({ ...parsedFilters.value, inStock: undefined });
  };

  const removeHasDiscount = () => {
    navigateWithFilters({ ...parsedFilters.value, hasDiscount: undefined });
  };

  const removeIsClearance = () => {
    navigateWithFilters({ ...parsedFilters.value, isClearance: undefined });
  };

  // Display Helpers
  const getBrandName = (brandSlug: string): string =>
    availableFilters.value?.brands.find((b) => b.slug === brandSlug)?.name ||
    brandSlug;

  const getAttributeValueName = (
    attrSlug: string,
    valueSlug: string
  ): string => {
    const attr = availableFilters.value?.attributes.find(
      (a) => a.slug === attrSlug
    );
    if (!attr) return valueSlug;

    const value = attr.values.find((v) => v.slug === valueSlug);
    return value ? `${attr.name}: ${value.value}` : `${attrSlug}: ${valueSlug}`;
  };

  // Sync sort with filters
  watchEffect(() => {
    selectedSort.value = parsedFilters.value.sortBy;
  });

  return {
    // State
    parentCategories,
    breadcrumbs,
    products,
    meta,
    availableFilters,
    parsedFilters,
    searchQuery,
    selectedSort,
    isLoading,
    currentPage,
    hasFilters,
    pageTitle,

    // Actions
    initializeData,
    fetchProducts,
    onFiltersChange,
    onSortChange,
    resetFilters,
    removeSearch,
    removeBrand,
    removePriceRange,
    removeAttributeValue,
    removeInStock,
    removeHasDiscount,
    removeIsClearance,

    // Helpers
    getBrandName,
    getAttributeValueName,
  };
}
