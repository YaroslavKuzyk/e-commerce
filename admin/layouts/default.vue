<template>
  <UApp>
    <NuxtLoadingIndicator />
    <UDashboardGroup>
      <VSidebar />
      <NuxtPage />
    </UDashboardGroup>
  </UApp>
</template>

<script setup lang="ts">
import VSidebar from "~/components/sidebar/VSidebar.vue";
</script>

<style scoped></style>
