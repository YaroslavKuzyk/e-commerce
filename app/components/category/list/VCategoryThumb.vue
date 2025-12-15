<template>
  <NuxtLink
    :to="`/category/${item.slug}`"
    class="group/category-thumb flex justify-between p-[22px] gap-3 items-end max-w-[570px] w-full ring ring-default rounded-[16px] transition hover:bg-elevated/50"
  >
    <div>
      <span v-if="item.subtitle" class="uppercase text-dimmed text-sm font-bold mb-[-12px] block">
        {{ item.subtitle }}
      </span>
      <p class="text-[42px] font-bold">{{ item.name }}</p>
    </div>
    <div class="relative overflow-hidden rounded-[10px]">
      <VSecureImage
        :fileId="item.menu_image_file_id"
        imgClass="max-w-[205px] w-full h-[256px] duration-200 group-hover/category-thumb:scale-110 object-cover object-top"
      />
    </div>
  </NuxtLink>
</template>

<script setup lang="ts">
import type { ProductCategory } from "~/models/productCategory";
import VSecureImage from "~/components/common/VSecureImage.vue";

interface IProps {
  item: ProductCategory;
}

const props = defineProps<IProps>();
</script>
