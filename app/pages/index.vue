<template>
  <UContainer>
    <div class="flex py-6">
      <VCategoriesList on-page>
        <div class="px-4">content</div>
      </VCategoriesList>
    </div>
  </UContainer>
</template>

<script setup lang="ts">
import VCategoriesList from "~/components/categories/list/VCategoriesList.vue";
</script>
