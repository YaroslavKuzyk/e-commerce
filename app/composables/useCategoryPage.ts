import { parseCategoryUrl, type ParsedFilters } from "~/utils/urlParser";
import { buildCategoryUrl, hasActiveFilters, filtersToApiParams } from "~/utils/urlBuilder";
import type { Product, AvailableFilters, FilterCategory } from "~/models/product";

export interface CategoryPageMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface BreadcrumbItem {
  label: string;
  to?: string;
}

export function useCategoryPage() {
  const route = useRoute();
  const router = useRouter();
  const config = useRuntimeConfig();
  const apiBase = config.public.sanctum?.baseUrl || "http://localhost:8000";

  // State
  const attributeSlugs = ref<string[]>([]);
  const currentCategory = ref<FilterCategory | null>(null);
  const breadcrumbs = ref<BreadcrumbItem[]>([]);
  const products = ref<Product[]>([]);
  const meta = ref<CategoryPageMeta | null>(null);
  const availableFilters = ref<AvailableFilters | null>(null);
  const parsedFilters = ref<ParsedFilters>({});
  const searchQuery = ref("");
  const selectedSort = ref<string | undefined>(undefined);
  const isLoading = ref(true);
  const currentPage = ref(1);
  const limit = 12;

  // Computed
  const slugArray = computed(() => {
    const slug = route.params.slug;
    if (Array.isArray(slug)) return slug;
    if (slug) return [slug];
    return [];
  });

  const categoryPath = computed(() => {
    const parsed = parseCategoryUrl(slugArray.value, attributeSlugs.value);
    return parsed.categoryPath;
  });

  const categorySlug = computed(() =>
    categoryPath.value.length > 0
      ? categoryPath.value[categoryPath.value.length - 1] ?? null
      : null
  );

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

  const resolveCategory = async () => {
    if (categoryPath.value.length === 0) {
      currentCategory.value = null;
      breadcrumbs.value = [{ label: "Каталог" }];
      return;
    }

    try {
      const slug = categoryPath.value[categoryPath.value.length - 1];
      const response = await $fetch<{
        success: boolean;
        data: {
          category: FilterCategory;
          breadcrumbs: FilterCategory[];
          category_id: number;
        };
      }>(`${apiBase}/api/product-categories/slug/${slug}`);

      currentCategory.value = response.data.category;
      breadcrumbs.value = buildBreadcrumbs(response.data.breadcrumbs);
    } catch (e) {
      console.error("Failed to resolve category:", e);
      currentCategory.value = null;
      breadcrumbs.value = [{ label: "Каталог" }];
    }
  };

  const fetchProducts = async () => {
    isLoading.value = true;

    try {
      const params = filtersToApiParams(
        categorySlug.value,
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
        meta: CategoryPageMeta;
      }>(`${apiBase}/api/products/by-slugs?${queryString}`);

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
      const params = new URLSearchParams();
      if (categorySlug.value) {
        params.append("category_slug", categorySlug.value);
      }

      const response = await $fetch<{
        success: boolean;
        data: AvailableFilters;
      }>(`${apiBase}/api/products/filters-by-slug?${params.toString()}`);

      availableFilters.value = response.data;
    } catch (e) {
      console.error("Failed to fetch filters:", e);
    }
  };

  // Helper Functions
  const buildBreadcrumbs = (categories: FilterCategory[]): BreadcrumbItem[] => [
    { label: "Каталог", to: "/category" },
    ...categories.map((cat, index) => ({
      label: cat.name,
      to:
        index === categories.length - 1
          ? undefined
          : `/category/${categories
              .slice(0, index + 1)
              .map((c) => c.slug)
              .join("/")}`,
    })),
  ];

  const parseUrl = () => {
    const parsed = parseCategoryUrl(slugArray.value, attributeSlugs.value);
    parsedFilters.value = parsed.filters;
  };

  const initializeData = async () => {
    await fetchAttributeSlugs();
    parseUrl();
    await Promise.all([resolveCategory(), fetchFilters()]);
    await fetchProducts();
  };

  // Navigation
  const navigateWithFilters = (newFilters: ParsedFilters) => {
    const url = buildCategoryUrl({
      categoryPath: categoryPath.value,
      filters: newFilters,
    });
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
    currentCategory,
    breadcrumbs,
    products,
    meta,
    availableFilters,
    parsedFilters,
    searchQuery,
    selectedSort,
    isLoading,
    currentPage,
    categoryPath,
    hasFilters,

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

    // Helpers
    getBrandName,
    getAttributeValueName,
  };
}
