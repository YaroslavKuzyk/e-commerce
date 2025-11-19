<template>
  <div v-if="meta" class="flex items-center justify-between gap-4 w-full">
    <!-- Left side: Per page selector and info -->
    <div class="flex items-center gap-4">
      <div class="flex items-center gap-2">
        <span class="text-sm text-gray-600 dark:text-gray-400"
          >Показувати:</span
        >
        <USelect
          :model-value="meta.per_page"
          :items="perPageOptions"
          @update:model-value="handlePerPageChange"
          class="w-[80px]"
        />
      </div>
      <span class="text-sm text-gray-600 dark:text-gray-400">
        Показано з {{ from }} по {{ to }} з {{ meta.total }} записів
      </span>
    </div>

    <!-- Right side: Pagination -->
    <UPagination
      v-model:page="page"
      :items-per-page="meta?.per_page || 15"
      :total="meta?.total || 0"
      :ui="{
        wrapper: 'flex items-center gap-1',
        rounded: 'first:rounded-l-md last:rounded-r-md',
        default: {
          activeButton: {
            variant: 'outline',
          },
          inactiveButton: {
            variant: 'outline',
          },
          prevButton: {
            variant: 'outline',
          },
          nextButton: {
            variant: 'outline',
          },
        },
      }"
    />
  </div>
</template>

<script setup lang="ts">
import type { IPaginationMeta } from "~/composables/usePaginationList";

interface IProps {
  meta: IPaginationMeta | null;
}

interface IEmits {
  (e: "update:page", value: number): void;
  (e: "update:perPage", value: number): void;
}

const props = defineProps<IProps>();
const emits = defineEmits<IEmits>();

const perPageOptions = [5, 10, 15, 25, 50];

const page = ref(props.meta?.current_page || 1);

const handlePerPageChange = (newPerPage: number) => {
  console.log("VPagination: per_page changed to", newPerPage);
  page.value = 1; // Reset to first page when changing per_page
  emits("update:perPage", newPerPage);
};

// Watch for external changes to meta.current_page
watch(
  () => props.meta?.current_page,
  (newPage) => {
    if (newPage && newPage !== page.value) {
      console.log("VPagination: syncing page from props", newPage);
      page.value = newPage;
    }
  }
);

// Watch for internal changes to page and emit
watch(page, (newPage, oldPage) => {
  console.log("VPagination: page changed", { newPage, oldPage });
  if (oldPage !== undefined) {
    emits("update:page", newPage);
  }
});

const from = computed(() => {
  if (!props.meta) return 0;
  return (props.meta.current_page - 1) * props.meta.per_page + 1;
});

const to = computed(() => {
  if (!props.meta) return 0;
  const calculatedTo = props.meta.current_page * props.meta.per_page;
  return calculatedTo > props.meta.total ? props.meta.total : calculatedTo;
});
</script>
