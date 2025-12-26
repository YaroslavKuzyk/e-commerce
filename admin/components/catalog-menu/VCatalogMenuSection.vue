<template>
  <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
    <!-- Section Header -->
    <div class="flex items-center gap-2 p-3 bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
      <UIcon
        name="i-heroicons-bars-3"
        class="w-4 h-4 cursor-move section-drag-handle text-gray-400"
      />
      <VSecureImage
        v-if="section.icon_file_id"
        :fileId="section.icon_file_id"
        class="w-5 h-5 rounded"
      />
      <div class="flex-1 min-w-0">
        <p class="font-medium text-sm truncate">{{ section.name }}</p>
        <p v-if="section.link" class="text-xs text-gray-500 truncate">{{ section.link }}</p>
      </div>
      <div class="flex items-center gap-1">
        <UButton
          size="xs"
          variant="ghost"
          icon="i-heroicons-plus"
          @click="emits('addItem')"
        />
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

    <!-- Section Items -->
    <div class="p-2">
      <div v-if="localItems.length === 0" class="text-center py-3 text-gray-400">
        <p class="text-xs">Немає посилань</p>
      </div>

      <draggable
        v-else
        v-model="localItems"
        item-key="id"
        handle=".item-drag-handle"
        ghost-class="opacity-50"
        class="space-y-1"
        @change="handleItemDragEnd"
      >
        <template #item="{ element: item }">
          <VCatalogMenuItem
            :item="item"
            @edit="emits('editItem', item)"
            @delete="emits('deleteItem', item)"
          />
        </template>
      </draggable>
    </div>
  </div>
</template>

<script setup lang="ts">
import draggable from "vuedraggable";
import VSecureImage from "~/components/common/VSecureImage.vue";
import VCatalogMenuItem from "./VCatalogMenuItem.vue";
import type { CatalogMenuSection, CatalogMenuItem } from "~/models/catalogMenu";

interface Props {
  section: CatalogMenuSection;
}

interface Emits {
  (e: "edit"): void;
  (e: "delete"): void;
  (e: "addItem"): void;
  (e: "editItem", item: CatalogMenuItem): void;
  (e: "deleteItem", item: CatalogMenuItem): void;
  (e: "reorderItems", itemIds: number[]): void;
}

const props = defineProps<Props>();
const emits = defineEmits<Emits>();

const localItems = ref<CatalogMenuItem[]>([]);

watch(
  () => props.section.items,
  (newItems) => {
    localItems.value = [...newItems];
  },
  { immediate: true, deep: true }
);

const handleItemDragEnd = () => {
  const itemIds = localItems.value.map((i) => i.id);
  emits("reorderItems", itemIds);
};
</script>
