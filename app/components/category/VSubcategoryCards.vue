<template>
  <div v-if="subcategories.length > 0" class="mb-6">
    <h2 class="text-2xl font-bold mb-4">{{ parentName }}</h2>
    <div class="flex gap-4 overflow-x-auto pb-2">
      <NuxtLink
        v-for="subcategory in subcategories"
        :key="subcategory.id"
        :to="buildSubcategoryUrl(subcategory.slug)"
        class="flex flex-col items-center gap-2 min-w-[120px] p-3 rounded-lg hover:bg-gray-50 transition-colors"
      >
        <div class="w-20 h-20 flex items-center justify-center">
          <VSecureImage
            v-if="subcategory.logo_file_id"
            :fileId="subcategory.logo_file_id"
            imgClass="max-w-full max-h-full object-contain"
          />
          <div v-else class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
            <Folder class="w-8 h-8 text-gray-400" />
          </div>
        </div>
        <span class="text-sm text-center line-clamp-2">{{ subcategory.name }}</span>
      </NuxtLink>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { Folder } from "lucide-vue-next";
import VSecureImage from "~/components/common/VSecureImage.vue";
import type { FilterCategory } from "~/models/product";

interface Props {
  parentName: string;
  parentSlug?: string;
  subcategories: FilterCategory[];
}

const props = defineProps<Props>();

const buildSubcategoryUrl = (subcategorySlug: string): string => {
  if (props.parentSlug) {
    return `/category/${props.parentSlug}/${subcategorySlug}`;
  }
  return `/category/${subcategorySlug}`;
};
</script>
