<template>
  <div class="mt-4">
    <UTable :data="attributes" :columns="columns" :loading="loading">
      <template #name-cell="{ row }">
        <span class="font-medium">{{ row.original.name }}</span>
      </template>

      <template #slug-cell="{ row }">
        <div class="text-sm text-gray-500">{{ row.original.slug }}</div>
      </template>

      <template #type-cell="{ row }">
        <UBadge variant="subtle" color="info">
          {{ typeLabels[row.original.type] }}
        </UBadge>
      </template>

      <template #status-cell="{ row }">
        <UBadge
          :color="row.original.status === 'published' ? 'success' : 'neutral'"
          variant="subtle"
        >
          {{ row.original.status === 'published' ? 'Опубліковано' : 'Чернетка' }}
        </UBadge>
      </template>

      <template #values-cell="{ row }">
        <div class="flex flex-wrap gap-1 max-w-[300px]">
          <template v-if="row.original.values && row.original.values.length > 0">
            <template v-if="row.original.type === 'color'">
              <div
                v-for="value in row.original.values.slice(0, 8)"
                :key="value.id"
                class="w-6 h-6 rounded-full border border-gray-300 cursor-pointer"
                :style="{ backgroundColor: value.color_code || '#ccc' }"
                :title="value.value"
              />
              <span v-if="row.original.values.length > 8" class="text-sm text-gray-500 ml-1">
                +{{ row.original.values.length - 8 }}
              </span>
            </template>
            <template v-else>
              <UBadge
                v-for="value in row.original.values.slice(0, 5)"
                :key="value.id"
                variant="soft"
                size="xs"
              >
                {{ value.value }}
              </UBadge>
              <UBadge v-if="row.original.values.length > 5" variant="soft" size="xs">
                +{{ row.original.values.length - 5 }}
              </UBadge>
            </template>
          </template>
          <span v-else class="text-gray-400 text-sm">Немає значень</span>
        </div>
      </template>

      <template #actions-cell="{ row }">
        <div class="flex items-center justify-end gap-2">
          <HasPermissions :required-permissions="['Update Attribute']">
            <UButton
              size="sm"
              variant="ghost"
              icon="i-heroicons-pencil"
              @click="emits('edit', row.original)"
            />
          </HasPermissions>
          <HasPermissions :required-permissions="['Delete Attribute']">
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
import type { Attribute } from "~/models/attribute";

interface Props {
  attributes: Attribute[];
  loading?: boolean;
}

interface Emits {
  (e: "edit", attribute: Attribute): void;
  (e: "delete", attribute: Attribute): void;
}

const props = defineProps<Props>();
const emits = defineEmits<Emits>();

const typeLabels: Record<string, string> = {
  select: 'Вибір',
  multi_select: 'Множинний вибір',
  checkbox: 'Чекбокс',
  switch: 'Перемикач',
  color: 'Колір',
};

const columns = [
  {
    id: "name",
    header: "Назва",
  },
  {
    id: "slug",
    header: "Slug",
    meta: { class: { th: "w-[150px]" } },
  },
  {
    id: "type",
    header: "Тип",
    meta: { class: { th: "w-[150px]" } },
  },
  {
    id: "status",
    header: "Статус",
    meta: { class: { th: "w-[120px]" } },
  },
  {
    id: "values",
    header: "Значення",
    meta: { class: { th: "w-[300px]" } },
  },
  {
    id: "actions",
    header: "Дії",
    meta: { class: { th: "w-[100px] text-right", td: "text-right" } },
  },
];
</script>
