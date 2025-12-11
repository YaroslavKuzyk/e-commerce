<template>
  <div>
    <div>
      <USeparator label="Топ Категорії" :ui="{ label: 'text-2xl' }" />
    </div>
    <div class="flex gap-6 flex-wrap py-6">
      <VCategoryThumb
        class="max-w-[50%]"
        v-for="(item, index) in 4"
        :key="index"
        :item="item"
      />
    </div>
    <div class="flex justify-center">
      <UButton variant="link">
        Категорії

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
</script>
