import { ref, computed, watch, type Ref } from "vue";
import type { AsyncData } from "#app";

interface UsePaginationListOptions<TFilter, TData> {
  filters: Ref<TFilter>;
  fetchMethod: (filters?: TFilter) => AsyncData<TData[] | undefined, any>;
  debounceFields?: (keyof TFilter)[];
  debounceDelay?: number;
}

export function usePaginationList<TFilter extends Record<string, any>, TData>({
  filters,
  fetchMethod,
  debounceFields = [],
  debounceDelay = 500,
}: UsePaginationListOptions<TFilter, TData>) {
  const data = ref<TData[]>([]) as Ref<TData[]>;
  const pending = ref(false);

  // Debounce timeout
  let debounceTimeout: ReturnType<typeof setTimeout> | null = null;

  const fetchData = async () => {
    pending.value = true;
    try {
      const activeFilters = {} as TFilter;

      // Build active filters (exclude null, undefined, empty strings)
      Object.keys(filters.value).forEach((key) => {
        const value = filters.value[key];
        if (value !== null && value !== undefined && value !== "") {
          activeFilters[key as keyof TFilter] = value;
        }
      });

      const { data: responseData } = await fetchMethod(
        Object.keys(activeFilters).length > 0 ? activeFilters : undefined
      );
      if (responseData.value) {
        data.value = responseData.value;
      }
    } catch (error) {
      console.error("Failed to fetch data:", error);
    } finally {
      pending.value = false;
    }
  };

  const hasActiveFilters = computed(() => {
    return Object.values(filters.value).some(
      (value) => value !== null && value !== undefined && value !== ""
    );
  });

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
            fetchData();
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
        fetchData();
      }
    );
  }

  const refresh = async () => {
    await fetchData();
  };

  return {
    data,
    pending,
    hasActiveFilters,
    clearFilters,
    refresh,
    fetchData,
  };
}
