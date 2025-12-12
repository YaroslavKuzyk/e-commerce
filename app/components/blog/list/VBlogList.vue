<template>
  <div>
    <div>
      <USeparator label="Нове в блозі" :ui="{ label: 'text-2xl' }" />
    </div>
    <div class="flex gap-6 flex-wrap py-6">
      <UBlogPosts orientation="vertical">
        <UBlogPost
          v-for="(post, index) in posts"
          :key="index"
          v-bind="post"
          :to="post.path"
          orientation="horizontal"
          class="h-[220px]"
        />
      </UBlogPosts>
    </div>
    <div class="flex justify-center">
      <UButton variant="link">
        Блог

        <template #trailing>
          <ArrowRight class="w-5 h-5" />
        </template>
      </UButton>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ArrowRight } from "lucide-vue-next";

const posts = ref([
  {
    title: "Nuxt Icon v1",
    description: "Discover Nuxt Icon v1!",
    image: "https://nuxt.com/assets/blog/nuxt-icon/cover.png",
    date: "2024-11-25",
    path: "/blog/nuxt-icon-v1",
  },
  {
    title: "Nuxt 3.14",
    description: "Nuxt 3.14 is out!",
    image: "https://nuxt.com/assets/blog/v3.14.png",
    date: "2024-11-04",
    path: "/blog/nuxt-3.14",
  },
  {
    title: "Nuxt 3.13",
    description: "Nuxt 3.13 is out!",
    image: "https://nuxt.com/assets/blog/v3.13.png",
    date: "2024-08-22",
    path: "/blog/nuxt-3.13",
  },
]);
</script>
