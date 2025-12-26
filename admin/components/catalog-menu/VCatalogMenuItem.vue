<template>
  <div
    class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
  >
    <UIcon
      name="i-heroicons-bars-2"
      class="w-3 h-3 cursor-move item-drag-handle text-gray-400"
    />
    <div class="flex-1 min-w-0">
      <p class="text-sm truncate">{{ item.name }}</p>
      <p class="text-xs text-gray-500 truncate">{{ item.link }}</p>
    </div>
    <div class="flex items-center gap-1">
      <UButton
        size="xs"
        variant="ghost"
        icon="i-heroicons-pencil"
        @click="emits('edit')"
      />
      <UButton
        size="xs"
        variant="ghost"
        color="error"
        icon="i-heroicons-trash"
        @click="emits('delete')"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import type { CatalogMenuItem } from "~/models/catalogMenu";

interface Props {
  item: CatalogMenuItem;
}

interface Emits {
  (e: "edit"): void;
  (e: "delete"): void;
}

defineProps<Props>();
const emits = defineEmits<Emits>();
</script>
