<template>
  <UPage>
    <UContainer>
      <div class="flex py-6">
        <VCategoryTreeItem on-page>
          <div class="px-4">
            <VHomeSlider :slides="slides" />
          </div>
        </VCategoryTreeItem>
      </div>
      <VProductList class="py-6" />
      <VCategoryList class="py-6" />
      <VBlogList class="py-6" />
    </UContainer>
  </UPage>
</template>

<script setup lang="ts">
import VCategoryTreeItem from "~/components/category/tree/VCategoryTreeItem.vue";
import VProductList from "~/components/product/list/VProductList.vue";
import VCategoryList from "~/components/category/list/VCategoryList.vue";
import VBlogList from "~/components/blog/list/VBlogList.vue";
import VHomeSlider from "~/components/home/VHomeSlider.vue";

const { slides } = useStoreSettings();
</script>
