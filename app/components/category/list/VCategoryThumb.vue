<template>
  <NuxtLink
    :to="`/category/${item.slug}`"
    class="group/category-thumb flex flex-col items-center gap-2 min-w-[120px] p-3 rounded-lg hover:bg-gray-50 transition-colors"
  >
    <div class="w-20 h-20 flex items-center justify-center">
      <VSecureImage
        v-if="item.logo_file_id || item.menu_image_file_id"
        :fileId="item.logo_file_id || item.menu_image_file_id"
        imgClass="max-w-full max-h-full object-contain"
      />
      <div v-else class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
        <Folder class="w-8 h-8 text-gray-400" />
      </div>
    </div>
    <span class="text-sm text-center line-clamp-2">{{ item.name }}</span>
  </NuxtLink>
</template>

<script setup lang="ts">
import { Folder } from "lucide-vue-next";
import type { ProductCategory } from "~/models/productCategory";
import VSecureImage from "~/components/common/VSecureImage.vue";

interface IProps {
  item: ProductCategory;
}

const props = defineProps<IProps>();
</script>
