<template>
  <div class="v-blog-category-tabs">
    <div class="flex gap-2 overflow-x-auto pb-2">
      <NuxtLink
        :to="'/blog'"
        :class="[
          'px-4 py-2 rounded-lg font-medium transition-colors whitespace-nowrap',
          !activeSlug
            ? 'bg-primary text-white'
            : 'bg-gray-100 hover:bg-gray-200 text-gray-700'
        ]"
      >
        Всі
        <span v-if="totalCount" class="ml-1 text-sm opacity-75">({{ totalCount }})</span>
      </NuxtLink>

      <NuxtLink
        v-for="category in categories"
        :key="category.id"
        :to="`/blog/${category.slug}`"
        :class="[
          'px-4 py-2 rounded-lg font-medium transition-colors whitespace-nowrap',
          activeSlug === category.slug
            ? 'bg-primary text-white'
            : 'bg-gray-100 hover:bg-gray-200 text-gray-700'
        ]"
      >
        {{ category.name }}
        <span v-if="category.posts_count" class="ml-1 text-sm opacity-75">({{ category.posts_count }})</span>
      </NuxtLink>
    </div>
  </div>
</template>

<script lang="ts" setup>
import type { BlogCategory } from "~/models/blog";

interface Props {
  categories: BlogCategory[];
  activeSlug?: string | null;
}

const props = defineProps<Props>();

const totalCount = computed(() => {
  return props.categories.reduce((sum, cat) => sum + (cat.posts_count || 0), 0);
});
</script>
