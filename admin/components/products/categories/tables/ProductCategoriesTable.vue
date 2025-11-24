<template>
  <div class="mt-4">
    <UTable :data="flattenedCategories" :columns="columns" :loading="loading">
      <template #name-header>
        <div class="flex items-center gap-2">
          <UButton
            size="xs"
            variant="ghost"
            :icon="allExpanded ? 'i-heroicons-chevron-up' : 'i-heroicons-chevron-down'"
            @click="toggleExpandAll"
            :title="allExpanded ? 'Згорнути всі' : 'Розгорнути всі'"
          />
          <span>Назва</span>
        </div>
      </template>

      <template #name-cell="{ row }">
        <div :style="{ paddingLeft: `${row.original.level * 24}px` }" class="flex items-center gap-2">
          <UButton
            v-if="row.original.subcategories_count && row.original.subcategories_count > 0"
            size="xs"
            variant="ghost"
            :icon="row.original.expanded ? 'i-heroicons-chevron-down' : 'i-heroicons-chevron-right'"
            @click="toggleExpand(row.original)"
          />
          <div v-else class="w-6" />
          <span class="font-medium">{{ row.original.name }}</span>
        </div>
      </template>

      <template #slug-cell="{ row }">
        <div class="text-sm text-gray-500">{{ row.original.slug }}</div>
      </template>

      <template #status-cell="{ row }">
        <UBadge
          :color="row.original.status === 'published' ? 'success' : 'neutral'"
          variant="subtle"
        >
          {{ row.original.status === 'published' ? 'Опубліковано' : 'Чернетка' }}
        </UBadge>
      </template>

      <template #subcategories_count-cell="{ row }">
        <div class="text-center">
          <UBadge v-if="row.original.subcategories_count && row.original.subcategories_count > 0" variant="subtle">
            {{ row.original.subcategories_count }}
          </UBadge>
          <span v-else class="text-gray-400">—</span>
        </div>
      </template>

      <template #logo_file_id-cell="{ row }">
        <div v-if="row.original.logo_file_id" class="flex items-center justify-center">
          <VSecureImage
            :file-id="row.original.logo_file_id"
            :alt="row.original.name"
            width="w-10"
            height="h-10"
            object-fit="cover"
            class="rounded"
          />
        </div>
        <span v-else class="text-gray-400 text-sm">Немає</span>
      </template>

      <template #actions-cell="{ row }">
        <div class="flex items-center justify-end gap-2">
          <HasPermissions :required-permissions="['Create Product Category']">
            <UButton
              size="sm"
              variant="ghost"
              icon="i-heroicons-plus"
              color="primary"
              @click="emits('createSubcategory', row.original)"
            />
          </HasPermissions>
          <HasPermissions :required-permissions="['Update Product Category']">
            <UButton
              size="sm"
              variant="ghost"
              icon="i-heroicons-pencil"
              @click="emits('edit', row.original)"
            />
          </HasPermissions>
          <HasPermissions :required-permissions="['Delete Product Category']">
            <UButton
              size="sm"
              variant="ghost"
              icon="i-heroicons-trash"
              color="error"
              @click="emits('delete', row.original)"
            />
          </HasPermissions>
        </div>
      </template>
    </UTable>
  </div>
</template>

<script setup lang="ts">
import HasPermissions from "~/components/common/VHasPermissions.vue";
import type { ProductCategory } from "~/models/productCategory";

interface Props {
  categories: ProductCategory[];
  loading?: boolean;
  hasActiveFilters?: boolean;
}

interface Emits {
  (e: "edit", category: ProductCategory): void;
  (e: "delete", category: ProductCategory): void;
  (e: "createSubcategory", category: ProductCategory): void;
}

const props = defineProps<Props>();
const emits = defineEmits<Emits>();

const columns = [
  {
    id: "name",
    header: "Назва",
  },
  {
    id: "slug",
    header: "Slug",
    meta: { class: { th: "w-[200px]" } },
  },
  {
    id: "status",
    header: "Статус",
    meta: { class: { th: "w-[120px]" } },
  },
  {
    id: "subcategories_count",
    header: "Підкатегорії",
    meta: { class: { th: "w-[120px] text-center" } },
  },
  {
    id: "logo_file_id",
    header: "Логотип",
    meta: { class: { th: "w-[100px] text-center" } },
  },
  {
    id: "actions",
    header: "Дії",
    meta: { class: { th: "w-[140px] text-right", td: "text-right" } },
  },
];

// Track expanded state
const expandedIds = ref<Set<number>>(new Set());

const toggleExpand = (category: ProductCategory) => {
  if (expandedIds.value.has(category.id)) {
    expandedIds.value.delete(category.id);
  } else {
    expandedIds.value.add(category.id);
  }
};

// Get all category IDs recursively
const getAllCategoryIds = (categories: ProductCategory[]): number[] => {
  const ids: number[] = [];
  const collect = (cats: ProductCategory[]) => {
    cats.forEach(cat => {
      if (cat.subcategories && cat.subcategories.length > 0) {
        ids.push(cat.id);
        collect(cat.subcategories);
      }
    });
  };
  collect(categories);
  return ids;
};

// Check if all categories are expanded
const allExpanded = computed(() => {
  const allIds = getAllCategoryIds(props.categories);
  return allIds.length > 0 && allIds.every(id => expandedIds.value.has(id));
});

// Toggle expand/collapse all
const toggleExpandAll = () => {
  const allIds = getAllCategoryIds(props.categories);

  if (allExpanded.value) {
    // Collapse all
    expandedIds.value.clear();
  } else {
    // Expand all
    expandedIds.value = new Set(allIds);
  }
};

// Auto-expand all categories when filters are active
watch(() => props.hasActiveFilters, (hasFilters) => {
  if (hasFilters) {
    const allIds = getAllCategoryIds(props.categories);
    expandedIds.value = new Set(allIds);
  }
});

// Flatten tree structure for table display
const flattenedCategories = computed(() => {
  const result: Array<ProductCategory & { level: number; expanded: boolean }> = [];

  const flatten = (
    cats: ProductCategory[],
    level: number = 0,
    parentExpanded: boolean = true
  ) => {
    if (!parentExpanded) return;

    cats.forEach((cat) => {
      const expanded = expandedIds.value.has(cat.id);
      result.push({ ...cat, level, expanded });

      if (cat.subcategories && cat.subcategories.length > 0 && expanded) {
        flatten(cat.subcategories, level + 1, expanded);
      }
    });
  };

  flatten(props.categories);
  return result;
});
</script>
