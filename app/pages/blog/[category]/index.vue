<template>
  <UPage>
    <UContainer>
      <VBreadcrumbs
        :items="breadcrumbs"
        class="pt-6"
      />

      <div class="py-6">
        <h1 class="text-3xl font-bold mb-6">
          {{ currentCategory?.name || 'Блог' }}
        </h1>

        <!-- Category Tabs -->
        <VBlogCategoryTabs
          :categories="categories"
          :active-slug="categorySlug"
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

const route = useRoute();

const {
  categories,
  currentCategory,
  posts,
  meta,
  isLoading,
  currentPage,
  fetchCategories,
  fetchCategoryBySlug,
  fetchPosts,
  buildBreadcrumbs,
} = useBlog();

const categorySlug = computed(() => route.params.category as string);

const breadcrumbs = computed(() => buildBreadcrumbs(currentCategory.value));

const loadData = async () => {
  currentPage.value = 1;
  await fetchCategories();
  await fetchCategoryBySlug(categorySlug.value);
  await fetchPosts(categorySlug.value);
};

onMounted(() => loadData());

watch(
  () => route.params.category,
  () => loadData()
);

watch(currentPage, () => fetchPosts(categorySlug.value));

useHead({
  title: computed(() => currentCategory.value?.name || "Блог"),
});
</script>
