<template>
  <main class="min-h-screen flex flex-col">
    <UApp class="flex flex-col flex-grow">
      <VHeader />
      <div class="flex-grow">
        <slot />
      </div>
      <VFooter class="mt-[100px]" />
    </UApp>
  </main>
</template>

<script setup lang="ts">
import VHeader from "~/components/common/VHeader.vue";
import VFooter from "~/components/common/VFooter.vue";
</script>

<style scoped></style>
