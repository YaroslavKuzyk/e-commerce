<template>
  <div
    class="flex items-center gap-3 p-3 rounded-[14px] cursor-pointer hover:bg-primary-50 duration-200"
    :class="{
      'bg-primary-50': active,
    }"
  >
    <VSecureImage
      :fileId="category.logo_file_id"
      :alt="category.name"
      :width="35"
      :height="35"
    />
    <span>{{ category.name }}</span>
    <ChevronRight class="ml-auto" />
  </div>
</template>

<script lang="ts" setup>
import { ChevronRight } from "lucide-vue-next";
import VSecureImage from "~/components/common/VSecureImage.vue";
import type { ProductCategory } from "~/models/productCategory";

interface IProps {
  category: ProductCategory;
  active: boolean;
}

const props = defineProps<IProps>();
</script>
