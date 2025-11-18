import { computed, watch, type Ref } from "vue";

interface UsePaginationListOptions<TFilter, TData> {
  key: string;
  filters: Ref<TFilter>;
  fetchFunction: (filters?: TFilter) => Promise<TData[]>;
  debounceFields?: (keyof TFilter)[];
  debounceDelay?: number;
}

export async function usePaginationList<
  TFilter extends Record<string, any>,
  TData
>({
  key,
  filters,
  fetchFunction,
  debounceFields = [],
  debounceDelay = 500,
}: UsePaginationListOptions<TFilter, TData>) {
  // Build active filters helper
  const buildFilters = () => {
    const activeFilters = {} as TFilter;
    Object.keys(filters.value).forEach((filterKey) => {
      const value = filters.value[filterKey];
      if (value !== null && value !== undefined && value !== "") {
        // Skip empty arrays
        if (Array.isArray(value) && value.length === 0) {
          return;
        }
        activeFilters[filterKey as keyof TFilter] = value;
      }
    });
    return Object.keys(activeFilters).length > 0 ? activeFilters : undefined;
  };

  // Використовуємо useAsyncData з функцією, яка залежить від фільтрів
  const { data, pending, refresh } = await useAsyncData<TData[]>(
    key,
    () => fetchFunction(buildFilters())
  );

  // Computed for active filters check
  const hasActiveFilters = computed(() => {
    return Object.values(filters.value).some((value) => {
      if (value === null || value === undefined || value === "") {
        return false;
      }
      // Check for empty arrays
      if (Array.isArray(value) && value.length === 0) {
        return false;
      }
      return true;
    });
  });

  // Clear all filters
  const clearFilters = () => {
    const newFilters = { ...filters.value } as Record<string, any>;
    Object.keys(newFilters).forEach((key) => {
      const value = newFilters[key];
      if (typeof value === "string") {
        newFilters[key] = "";
      } else if (typeof value === "number") {
        newFilters[key] = null;
      } else if (Array.isArray(value)) {
        newFilters[key] = [];
      } else {
        newFilters[key] = null;
      }
    });
    filters.value = newFilters as TFilter;
  };

  // Wrapper для refresh
  const refreshData = async () => {
    try {
      await refresh();
      return data.value;
    } catch (error) {
      throw error;
    }
  };

  // Debounce timeout
  let debounceTimeout: ReturnType<typeof setTimeout> | null = null;

  // Watch debounce fields with delay
  if (debounceFields.length > 0) {
    debounceFields.forEach((field) => {
      watch(
        () => filters.value[field],
        () => {
          if (debounceTimeout) {
            clearTimeout(debounceTimeout);
          }
          debounceTimeout = setTimeout(() => {
            refreshData();
          }, debounceDelay);
        }
      );
    });
  }

  // Watch non-debounce fields instantly
  const instantFields = Object.keys(filters.value).filter(
    (key) => !debounceFields.includes(key as keyof TFilter)
  );

  if (instantFields.length > 0) {
    watch(
      () => instantFields.map((field) => filters.value[field]),
      () => {
        refreshData();
      },
      { deep: true }
    );
  }

  return {
    data,
    pending,
    hasActiveFilters,
    clearFilters,
    refresh: refreshData,
  };
}
