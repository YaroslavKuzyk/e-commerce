<template>
  <UContainer>
    <div class="flex py-6">
      <VCategoryTreeItem on-page>
        <div class="px-4">
          <UCarousel
            v-slot="{ item }"
            loop
            arrows
            dots
            :autoplay="{ delay: 9000 }"
            :items="items"
            class="w-full max-w-[100%]"
            :ui="{
              container: 'transition-[height]',
              controls: 'absolute -bottom-8 inset-x-12',
              dots: '-top-7',
              dot: 'w-6 h-1',
            }"
            :prev="{ variant: 'solid' }"
            :next="{ variant: 'solid' }"
          >
            <img :src="item" class="rounded-lg w-full h-[400px] object-cover" />
          </UCarousel>
        </div>
      </VCategoryTreeItem>
    </div>
    <VProductList class="py-6" />
    <VCategoryList class="py-6" />
  </UContainer>
</template>

<script setup lang="ts">
import VCategoryTreeItem from "~/components/category/tree/VCategoryTreeItem.vue";
import VProductList from "~/components/product/list/VProductList.vue";
import VCategoryList from "~/components/category/list/VCategoryList.vue";

const items = [
  "https://picsum.photos/640/640?random=1",
  "https://picsum.photos/640/640?random=2",
  "https://picsum.photos/640/640?random=3",
  "https://picsum.photos/640/640?random=4",
  "https://picsum.photos/640/640?random=5",
  "https://picsum.photos/640/640?random=6",
];
</script>
