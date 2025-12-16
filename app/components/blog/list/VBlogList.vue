<template>
  <div>
    <div>
      <USeparator label="Нове в блозі" :ui="{ label: 'text-2xl' }" />
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex gap-6 flex-wrap py-6">
      <div
        v-for="i in 3"
        :key="i"
        class="w-full md:w-[calc(33.333%-1rem)] bg-white rounded-lg overflow-hidden shadow-sm animate-pulse"
      >
        <div class="aspect-video bg-gray-200" />
        <div class="p-4 space-y-2">
          <div class="h-4 bg-gray-200 rounded w-1/4" />
          <div class="h-5 bg-gray-200 rounded w-3/4" />
          <div class="h-4 bg-gray-200 rounded w-full" />
        </div>
      </div>
    </div>

    <!-- Posts -->
    <div v-else-if="posts.length" class="grid grid-cols-1 md:grid-cols-3 gap-6 py-6">
      <VBlogPostCard
        v-for="post in posts"
        :key="post.id"
        :post="post"
      />
    </div>

    <!-- Empty State -->
    <div v-else class="py-12 text-center text-dimmed">
      Поки немає публікацій
    </div>

    <div class="flex justify-center">
      <UButton variant="link" to="/blog">
        Всі публікації

        <template #trailing>
          <ArrowRight class="w-5 h-5" />
        </template>
      </UButton>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ArrowRight } from "lucide-vue-next";
import VBlogPostCard from "~/components/blog/VBlogPostCard.vue";
import type { BlogPost } from "~/models/blog";

const config = useRuntimeConfig();
const apiBase = config.public.sanctum?.baseUrl || "http://localhost:8000";

const posts = ref<BlogPost[]>([]);
const isLoading = ref(true);

const fetchLatestPosts = async () => {
  isLoading.value = true;
  try {
    const response = await $fetch<{
      success: boolean;
      data: BlogPost[];
    }>(`${apiBase}/api/blog/posts?limit=3`);
    posts.value = response.data;
  } catch (e) {
    console.error("Failed to fetch blog posts:", e);
    posts.value = [];
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => fetchLatestPosts());
</script>
