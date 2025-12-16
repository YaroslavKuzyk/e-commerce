<template>
  <UPage>
    <UContainer>
      <VBreadcrumbs
        :items="breadcrumbs"
        class="pt-6"
      />

      <!-- Loading State -->
      <div v-if="isLoading" class="py-8">
        <div class="animate-pulse space-y-4">
          <div class="h-8 bg-gray-200 rounded w-2/3" />
          <div class="h-4 bg-gray-200 rounded w-1/4" />
          <div class="aspect-video bg-gray-200 rounded-lg" />
          <div class="space-y-2">
            <div class="h-4 bg-gray-200 rounded w-full" />
            <div class="h-4 bg-gray-200 rounded w-full" />
            <div class="h-4 bg-gray-200 rounded w-3/4" />
          </div>
        </div>
      </div>

      <!-- Post Not Found -->
      <div v-else-if="!currentPost" class="py-16 text-center">
        <FileText class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <h2 class="text-2xl font-semibold text-gray-600 mb-2">Публікацію не знайдено</h2>
        <p class="text-dimmed mb-4">Можливо, вона була видалена або переміщена</p>
        <UButton to="/blog">Повернутись до блогу</UButton>
      </div>

      <!-- Post Content -->
      <article v-else class="py-8">
        <!-- Header -->
        <header class="mb-8">
          <div class="flex items-center gap-3 text-sm text-dimmed mb-4">
            <NuxtLink
              v-if="currentPost.category"
              :to="`/blog/${currentPost.category.slug}`"
              class="text-primary font-medium hover:underline"
            >
              {{ currentPost.category.name }}
            </NuxtLink>
            <span v-if="currentPost.category">•</span>
            <span>{{ formatDate(currentPost.publication_date) }}</span>
          </div>

          <h1 class="text-3xl md:text-4xl font-bold mb-4">
            {{ currentPost.title }}
          </h1>

          <p v-if="currentPost.short_description" class="text-lg text-dimmed">
            {{ currentPost.short_description }}
          </p>
        </header>

        <!-- Preview Image -->
        <div
          v-if="currentPost.preview_image_id"
          class="aspect-video bg-gray-100 rounded-lg overflow-hidden mb-8"
        >
          <VSecureImage
            :fileId="currentPost.preview_image_id"
            imgClass="w-full h-full object-cover"
          />
        </div>

        <!-- Content -->
        <div
          class="prose prose-lg max-w-none mb-12"
          v-html="currentPost.content"
        />

        <!-- Related Products -->
        <div v-if="currentPost.products?.length" class="mb-12">
          <h2 class="text-2xl font-bold mb-6">Пов'язані товари</h2>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <VProductThumb
              v-for="product in currentPost.products"
              :key="product.id"
              :product="product"
            />
          </div>
        </div>

        <!-- Related Posts -->
        <div v-if="relatedPosts.length" class="border-t pt-12">
          <h2 class="text-2xl font-bold mb-6">Схожі публікації</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <VBlogPostCard
              v-for="post in relatedPosts"
              :key="post.id"
              :post="post"
            />
          </div>
        </div>
      </article>
    </UContainer>
  </UPage>
</template>

<script setup lang="ts">
import { FileText } from "lucide-vue-next";
import VBreadcrumbs from "~/components/common/VBreadcrumbs.vue";
import VSecureImage from "~/components/common/VSecureImage.vue";
import VBlogPostCard from "~/components/blog/VBlogPostCard.vue";
import VProductThumb from "~/components/product/list/VProductThumb.vue";

const route = useRoute();

const {
  currentPost,
  relatedPosts,
  isLoading,
  fetchPostBySlug,
  fetchRelatedPosts,
  buildBreadcrumbs,
} = useBlog();

const postSlug = computed(() => route.params.post as string);

const breadcrumbs = computed(() =>
  buildBreadcrumbs(currentPost.value?.category, currentPost.value)
);

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString("uk-UA", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
};

const loadData = async () => {
  await fetchPostBySlug(postSlug.value);
  if (currentPost.value) {
    await fetchRelatedPosts(postSlug.value);
  }
};

onMounted(() => loadData());

watch(
  () => route.params.post,
  () => loadData()
);

useHead({
  title: computed(() => currentPost.value?.title || "Публікація"),
  meta: [
    {
      name: "description",
      content: computed(() => currentPost.value?.short_description || ""),
    },
  ],
});
</script>

<style scoped>
.prose :deep(img) {
  border-radius: 0.5rem;
}

.prose :deep(a) {
  color: var(--ui-primary);
}

.prose :deep(a:hover) {
  text-decoration: underline;
}
</style>
