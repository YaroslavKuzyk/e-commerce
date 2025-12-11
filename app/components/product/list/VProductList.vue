<template>
  <div>
    <div>
      <USeparator label="Нові Продукти" :ui="{ label: 'text-2xl' }" />
    </div>
    <div class="flex gap-6 flex-wrap py-6">
      <VProductThumb
        class="w-[calc(25%-18px)]"
        v-for="(item, index) in 8"
        :key="index"
      />
    </div>
    <div class="flex justify-center">
      <UButton variant="link">
        Продукти

        <template #trailing>
          <ArrowRight class="w-5 h-5" />
        </template>
      </UButton>
    </div>
  </div>
</template>

<script setup lang="ts">
import VProductThumb from "~/components/product/list/VProductThumb.vue";
import { ArrowRight } from "lucide-vue-next";
</script>
