<template>
  <NuxtLink
    :to="postUrl"
    class="v-blog-post-card group block bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow"
  >
    <!-- Image -->
    <div class="aspect-video bg-gray-100 overflow-hidden">
      <VSecureImage
        v-if="post.preview_image_id"
        :fileId="post.preview_image_id"
        imgClass="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
      />
      <div v-else class="w-full h-full flex items-center justify-center">
        <FileText class="w-12 h-12 text-gray-300" />
      </div>
    </div>

    <!-- Content -->
    <div class="p-4">
      <!-- Category & Date -->
      <div class="flex items-center gap-2 text-sm text-dimmed mb-2">
        <span v-if="post.category" class="text-primary font-medium">
          {{ post.category.name }}
        </span>
        <span v-if="post.category">•</span>
        <span>{{ formatDate(post.publication_date) }}</span>
      </div>

      <!-- Title -->
      <h3 class="font-semibold text-lg mb-2 line-clamp-2 group-hover:text-primary transition-colors">
        {{ post.title }}
      </h3>

      <!-- Short Description -->
      <p v-if="post.short_description" class="text-dimmed text-sm line-clamp-3">
        {{ post.short_description }}
      </p>

      <!-- Read More -->
      <div class="mt-3 flex items-center gap-1 text-primary font-medium text-sm">
        <span>Читати далі</span>
        <ArrowRight class="w-4 h-4" />
      </div>
    </div>
  </NuxtLink>
</template>

<script lang="ts" setup>
import { FileText, ArrowRight } from "lucide-vue-next";
import VSecureImage from "~/components/common/VSecureImage.vue";
import type { BlogPost } from "~/models/blog";

interface Props {
  post: BlogPost;
}

const props = defineProps<Props>();

const postUrl = computed(() => {
  if (props.post.category?.slug) {
    return `/blog/${props.post.category.slug}/${props.post.slug}`;
  }
  return `/blog/all/${props.post.slug}`;
});

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString("uk-UA", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
};
</script>
