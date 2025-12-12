<template>
  <div
    class="flex items-center gap-3 p-3 rounded-[14px] cursor-pointer hover:bg-primary-50 duration-200"
    :class="{
      'bg-primary-50': active,
    }"
  >
    <!-- <img :src="category.menu_image_file_id" :alt="category.name" /> -->
    <span>{{ category.name }}</span>
    <ChevronRight class="ml-auto" />
  </div>
</template>

<script lang="ts" setup>
import { ChevronRight } from "lucide-vue-next";
import type { ProductCategory } from "~/models/productCategory";

interface IProps {
  category: ProductCategory;
  active: boolean;
}

const props = defineProps<IProps>();
</script>
