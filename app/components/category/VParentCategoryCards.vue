<template>
  <div v-if="categories.length > 0" class="mb-6">
    <h2 v-if="title" class="text-2xl font-bold mb-4">{{ title }}</h2>
    <div class="flex gap-4 overflow-x-auto pb-2">
      <NuxtLink
        v-for="category in categories"
        :key="category.id"
        :to="`/category/${category.slug}`"
        class="flex flex-col items-center gap-2 min-w-[120px] p-3 rounded-lg hover:bg-gray-50 transition-colors"
      >
        <div class="w-20 h-20 flex items-center justify-center">
          <VSecureImage
            v-if="category.logo_file_id"
            :fileId="category.logo_file_id"
            imgClass="max-w-full max-h-full object-contain"
          />
          <div v-else class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
            <Folder class="w-8 h-8 text-gray-400" />
          </div>
        </div>
        <span class="text-sm text-center line-clamp-2">{{ category.name }}</span>
      </NuxtLink>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { Folder } from "lucide-vue-next";
import VSecureImage from "~/components/common/VSecureImage.vue";

interface ParentCategory {
  id: number;
  name: string;
  slug: string;
  logo_file_id?: number | null;
}

interface Props {
  title?: string;
  categories: ParentCategory[];
}

withDefaults(defineProps<Props>(), {
  title: "Категорії",
});
</script>
