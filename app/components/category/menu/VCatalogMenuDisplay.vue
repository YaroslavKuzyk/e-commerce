<template>
  <div v-if="menu && menu.sections && menu.sections.length > 0" class="grid grid-cols-3 gap-6">
    <div v-for="column in [1, 2, 3]" :key="column" class="space-y-4">
      <div
        v-for="section in getSectionsByColumn(column)"
        :key="section.id"
        class="space-y-2"
      >
        <NuxtLink
          v-if="section.link"
          :to="section.link"
          class="flex items-center gap-2 font-medium text-gray-900 hover:text-primary-600 transition-colors"
        >
          <VSecureImage
            v-if="section.icon_file_id"
            :fileId="section.icon_file_id"
            class="w-5 h-5"
          />
          {{ section.name }}
        </NuxtLink>
        <div
          v-else
          class="flex items-center gap-2 font-medium text-gray-900"
        >
          <VSecureImage
            v-if="section.icon_file_id"
            :fileId="section.icon_file_id"
            class="w-5 h-5"
          />
          {{ section.name }}
        </div>

        <div class="space-y-1 pl-7">
          <NuxtLink
            v-for="item in section.items"
            :key="item.id"
            :to="item.link"
            class="block text-sm text-gray-600 hover:text-primary-600 transition-colors py-1"
          >
            {{ item.name }}
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { CatalogMenu, CatalogMenuSection } from "~/models/catalogMenu";
import VSecureImage from "~/components/common/VSecureImage.vue";

interface Props {
  menu: CatalogMenu | null;
}

const props = defineProps<Props>();

const getSectionsByColumn = (column: number): CatalogMenuSection[] => {
  if (!props.menu || !props.menu.sections) return [];
  return props.menu.sections
    .filter((s) => s.column_index === column)
    .sort((a, b) => a.sort_order - b.sort_order);
};
</script>
