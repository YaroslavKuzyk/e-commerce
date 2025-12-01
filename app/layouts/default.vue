<template>
  <UApp>
    <VHeader />
    <slot />
  </UApp>
</template>

<script setup lang="ts">
import VHeader from "~/components/common/VHeader.vue";
</script>

<style scoped></style>
