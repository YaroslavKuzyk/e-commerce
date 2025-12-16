<template>
  <UPage>
    <UContainer>
      <VBreadcrumbs
        :items="breadcrumbs"
        class="pt-6"
      />

      <div class="py-6">
        <h1 class="text-3xl font-bold mb-6">Блог</h1>

        <!-- Category Tabs -->
        <VBlogCategoryTabs
          :categories="categories"
          :active-slug="null"
          class="mb-8"
        />

        <!-- Posts Grid -->
        <VBlogPostsGrid
          :posts="posts"
          :is-loading="isLoading"
        />

        <!-- Pagination -->
        <div
          v-if="meta && meta.last_page > 1"
          class="flex justify-center mt-8"
        >
          <UPagination
            v-model="currentPage"
            :page-count="meta.per_page"
            :total="meta.total"
          />
        </div>
      </div>
    </UContainer>
  </UPage>
</template>

<script setup lang="ts">
import VBreadcrumbs from "~/components/common/VBreadcrumbs.vue";
import VBlogCategoryTabs from "~/components/blog/VBlogCategoryTabs.vue";
import VBlogPostsGrid from "~/components/blog/VBlogPostsGrid.vue";

const {
  categories,
  posts,
  meta,
  isLoading,
  currentPage,
  fetchCategories,
  fetchPosts,
  buildBreadcrumbs,
} = useBlog();

const breadcrumbs = computed(() => buildBreadcrumbs());

onMounted(async () => {
  await fetchCategories();
  await fetchPosts();
});

watch(currentPage, () => fetchPosts());

useHead({
  title: "Блог",
});
</script>
