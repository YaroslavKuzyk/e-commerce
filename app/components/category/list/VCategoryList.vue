<template>
  <div>
    <div>
      <USeparator label="Нові Категорії" :ui="{ label: 'text-2xl' }" />
    </div>
    <div class="flex gap-4 overflow-x-auto pb-2 py-6">
      <VCategoryThumb
        v-for="category in categories"
        :key="category.id"
        :item="category"
      />
    </div>
    <div class="flex justify-center">
      <UButton variant="link" to="/category">
        Всі категорії

        <template #trailing>
          <ArrowRight class="w-5 h-5" />
        </template>
      </UButton>
    </div>
  </div>
</template>

<script setup lang="ts">
import VCategoryThumb from "~/components/category/list/VCategoryThumb.vue";
import { ArrowRight } from "lucide-vue-next";

const productCategoryStore = useProductCategoryStore();
const { data: categories } = await productCategoryStore.fetchLatestCategories(4);
</script>
