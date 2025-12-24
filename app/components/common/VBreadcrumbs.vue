<template>
  <nav class="py-2" aria-label="Breadcrumb">
    <ol class="flex items-center gap-2 text-sm">
      <li v-for="(item, index) in items" :key="index" class="flex items-center gap-2">
        <template v-if="index > 0">
          <ChevronRight class="w-4 h-4 text-dimmed" />
        </template>
        <NuxtLink
          v-if="item.to"
          :to="item.to"
          class="text-dimmed hover:text-foreground transition-colors"
        >
          {{ item.label }}
        </NuxtLink>
        <span v-else class="text-foreground font-medium">
          {{ item.label }}
        </span>
      </li>
    </ol>
  </nav>
</template>

<script setup lang="ts">
import { ChevronRight } from "lucide-vue-next";

export interface BreadcrumbItem {
  label: string;
  to?: string;
}

defineProps<{
  items: BreadcrumbItem[];
}>();
</script>
