<template>
  <VSidebarContent :title="$t('common.main')">
    <template #toolbar>
    </template>
  </VSidebarContent>
</template>

<script setup lang="ts">
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";

definePageMeta({
  middleware: ["sanctum:auth"],
});
</script>
