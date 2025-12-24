<template>
  <div class="v-blog-posts-grid">
    <!-- Loading State -->
    <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="i in 6"
        :key="i"
        class="bg-white rounded-lg overflow-hidden shadow-sm animate-pulse"
      >
        <div class="aspect-video bg-gray-200" />
        <div class="p-4 space-y-3">
          <div class="h-4 bg-gray-200 rounded w-1/3" />
          <div class="h-6 bg-gray-200 rounded w-full" />
          <div class="h-4 bg-gray-200 rounded w-2/3" />
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="!posts.length"
      class="text-center py-16"
    >
      <FileText class="w-16 h-16 text-gray-300 mx-auto mb-4" />
      <h3 class="text-xl font-semibold text-gray-600 mb-2">Поки немає публікацій</h3>
      <p class="text-dimmed">Скоро тут з'являться цікаві статті</p>
    </div>

    <!-- Posts Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <VBlogPostCard
        v-for="post in posts"
        :key="post.id"
        :post="post"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
import { FileText } from "lucide-vue-next";
import VBlogPostCard from "./VBlogPostCard.vue";
import type { BlogPost } from "~/models/blog";

interface Props {
  posts: BlogPost[];
  isLoading?: boolean;
}

defineProps<Props>();
</script>
