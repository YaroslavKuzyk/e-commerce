<template>
  <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 min-h-[300px]">
    <div class="flex items-center justify-between mb-4">
      <h4 class="font-medium text-gray-700 dark:text-gray-300">
        Колонка {{ column }}
      </h4>
      <UButton
        size="xs"
        variant="soft"
        icon="i-heroicons-plus"
        @click="emits('addSection', column)"
      >
        Додати
      </UButton>
    </div>

    <div v-if="localSections.length === 0" class="text-center py-8 text-gray-400">
      <UIcon name="i-heroicons-squares-2x2" class="w-8 h-8 mx-auto mb-2" />
      <p class="text-sm">Немає секцій</p>
    </div>

    <draggable
      v-else
      v-model="localSections"
      item-key="id"
      handle=".section-drag-handle"
      ghost-class="opacity-50"
      class="space-y-3"
      @change="handleSectionDragEnd"
    >
      <template #item="{ element: section }">
        <VCatalogMenuSection
          :section="section"
          @edit="emits('editSection', section)"
          @delete="emits('deleteSection', section)"
          @add-item="emits('addItem', section.id)"
          @edit-item="(item) => emits('editItem', item)"
          @delete-item="(item) => emits('deleteItem', item)"
          @reorder-items="(itemIds) => emits('reorderItems', section.id, itemIds)"
        />
      </template>
    </draggable>
  </div>
</template>

<script setup lang="ts">
import draggable from "vuedraggable";
import VCatalogMenuSection from "./VCatalogMenuSection.vue";
import type { CatalogMenuSection, CatalogMenuItem } from "~/models/catalogMenu";

interface Props {
  column: 1 | 2 | 3;
  sections: CatalogMenuSection[];
  menuId: number;
}

interface Emits {
  (e: "addSection", column: number): void;
  (e: "editSection", section: CatalogMenuSection): void;
  (e: "deleteSection", section: CatalogMenuSection): void;
  (e: "addItem", sectionId: number): void;
  (e: "editItem", item: CatalogMenuItem): void;
  (e: "deleteItem", item: CatalogMenuItem): void;
  (e: "reorderSections", column: number, sectionIds: number[]): void;
  (e: "reorderItems", sectionId: number, itemIds: number[]): void;
}

const props = defineProps<Props>();
const emits = defineEmits<Emits>();

const localSections = ref<CatalogMenuSection[]>([]);

watch(
  () => props.sections,
  (newSections) => {
    localSections.value = [...newSections];
  },
  { immediate: true, deep: true }
);

const handleSectionDragEnd = () => {
  const sectionIds = localSections.value.map((s) => s.id);
  emits("reorderSections", props.column, sectionIds);
};
</script>
