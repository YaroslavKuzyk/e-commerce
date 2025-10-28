import { computed, watch, type Ref } from "vue";
import type { AsyncData } from "#app";

interface UsePaginationListOptions<TFilter, TData> {
  key: string;
  filters: Ref<TFilter>;
  fetchMethod: (filters?: TFilter) => AsyncData<TData[] | undefined, any>;
  debounceFields?: (keyof TFilter)[];
  debounceDelay?: number;
}

export async function usePaginationList<TFilter extends Record<string, any>, TData>({
  key,
  filters,
  fetchMethod,
  debounceFields = [],
  debounceDelay = 500,
}: UsePaginationListOptions<TFilter, TData>) {
  // Build active filters helper
  const buildFilters = () => {
    const activeFilters = {} as TFilter;
    Object.keys(filters.value).forEach((filterKey) => {
      const value = filters.value[filterKey];
      if (value !== null && value !== undefined && value !== "") {
        activeFilters[filterKey as keyof TFilter] = value;
      }
    });
    return Object.keys(activeFilters).length > 0 ? activeFilters : undefined;
  };

  // Use useAsyncData for SSR support
  const {
    data,
    pending,
    refresh,
  } = await useAsyncData(
    key,
    async () => {
      const { data: responseData } = await fetchMethod(buildFilters());
      return responseData.value || [];
    },
    {
      server: true,
      lazy: false,
    }
  );

  // Computed for active filters check
  const hasActiveFilters = computed(() => {
    return Object.values(filters.value).some(
      (value) => value !== null && value !== undefined && value !== ""
    );
  });

  // Clear all filters
  const clearFilters = () => {
    Object.keys(filters.value).forEach((key) => {
      const value = filters.value[key];
      if (typeof value === "string") {
        filters.value[key] = "" as any;
      } else if (typeof value === "number") {
        filters.value[key] = null as any;
      } else if (Array.isArray(value)) {
        filters.value[key] = [] as any;
      } else {
        filters.value[key] = null as any;
      }
    });
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
            refresh();
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
        refresh();
      }
    );
  }

  return {
    data,
    pending,
    hasActiveFilters,
    clearFilters,
    refresh,
  };
}
