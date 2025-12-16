<template>
  <div class="mb-6">
    <div class="flex items-center justify-between gap-4 mb-4">
      <div>
        <h1 class="text-3xl font-bold">
          {{ categoryName || "Каталог" }}
        </h1>
        <p class="text-dimmed">
          {{ total ? `Знайдено ${total} товарів` : "Всі товари" }}
        </p>
      </div>

      <div class="flex items-center gap-4">
        <UInput
          :model-value="search"
          placeholder="Пошук товарів..."
          icon="i-lucide-search"
          class="w-64"
          @update:model-value="$emit('update:search', $event)"
          @input="$emit('search')"
        />

        <USelectMenu
          :model-value="sort"
          :items="sortOptions"
          value-key="value"
          label-key="label"
          placeholder="Сортування"
          class="w-48"
          @update:model-value="onSortUpdate"
        />

        <UButton
          v-if="hasFilters"
          variant="outline"
          color="neutral"
          @click="$emit('reset')"
        >
          Скинути фільтри
        </UButton>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface SortOption {
  value: string;
  label: string;
}

interface Props {
  categoryName?: string | null;
  total?: number | null;
  search: string;
  sort?: string;
  sortOptions: SortOption[];
  hasFilters: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
  "update:search": [value: string];
  "update:sort": [value: string | undefined];
  search: [];
  "sort-change": [];
  reset: [];
}>();

const onSortUpdate = (value: string | undefined) => {
  emit("update:sort", value);
  emit("sort-change");
};
</script>
