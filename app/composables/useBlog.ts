import type { BlogCategory, BlogPost, BlogMeta } from "~/models/blog";

export function useBlog() {
  const config = useRuntimeConfig();
  const apiBase = config.public.sanctum?.baseUrl || "http://localhost:8000";

  // State
  const categories = ref<BlogCategory[]>([]);
  const currentCategory = ref<BlogCategory | null>(null);
  const posts = ref<BlogPost[]>([]);
  const currentPost = ref<BlogPost | null>(null);
  const relatedPosts = ref<BlogPost[]>([]);
  const meta = ref<BlogMeta | null>(null);
  const isLoading = ref(true);
  const currentPage = ref(1);
  const limit = 12;

  // Fetch all categories
  const fetchCategories = async () => {
    try {
      const response = await $fetch<{
        success: boolean;
        data: BlogCategory[];
      }>(`${apiBase}/api/blog/categories`);
      categories.value = response.data;
    } catch (e) {
      console.error("Failed to fetch blog categories:", e);
      categories.value = [];
    }
  };

  // Fetch category by slug
  const fetchCategoryBySlug = async (slug: string) => {
    try {
      const response = await $fetch<{
        success: boolean;
        data: BlogCategory;
      }>(`${apiBase}/api/blog/categories/${slug}`);
      currentCategory.value = response.data;
    } catch (e) {
      console.error("Failed to fetch blog category:", e);
      currentCategory.value = null;
    }
  };

  // Fetch posts with optional category filter
  const fetchPosts = async (categorySlug?: string | null) => {
    isLoading.value = true;
    try {
      const params = new URLSearchParams();
      params.append("page", currentPage.value.toString());
      params.append("limit", limit.toString());

      if (categorySlug) {
        params.append("category_slug", categorySlug);
      }

      const response = await $fetch<{
        success: boolean;
        data: BlogPost[];
        meta: BlogMeta;
      }>(`${apiBase}/api/blog/posts?${params.toString()}`);

      posts.value = response.data;
      meta.value = response.meta;
    } catch (e) {
      console.error("Failed to fetch blog posts:", e);
      posts.value = [];
      meta.value = null;
    } finally {
      isLoading.value = false;
    }
  };

  // Fetch single post by slug
  const fetchPostBySlug = async (slug: string) => {
    isLoading.value = true;
    try {
      const response = await $fetch<{
        success: boolean;
        data: BlogPost;
      }>(`${apiBase}/api/blog/posts/${slug}`);
      currentPost.value = response.data;
    } catch (e) {
      console.error("Failed to fetch blog post:", e);
      currentPost.value = null;
    } finally {
      isLoading.value = false;
    }
  };

  // Fetch related posts
  const fetchRelatedPosts = async (postSlug: string, count: number = 4) => {
    try {
      const response = await $fetch<{
        success: boolean;
        data: BlogPost[];
      }>(`${apiBase}/api/blog/posts/${postSlug}/related?limit=${count}`);
      relatedPosts.value = response.data;
    } catch (e) {
      console.error("Failed to fetch related posts:", e);
      relatedPosts.value = [];
    }
  };

  // Build breadcrumbs
  const buildBreadcrumbs = (category?: BlogCategory | null, post?: BlogPost | null) => {
    const items = [
      { label: "Головна", to: "/" },
      { label: "Блог", to: "/blog" },
    ];

    if (category) {
      items.push({
        label: category.name,
        to: post ? `/blog/${category.slug}` : undefined,
      });
    }

    if (post) {
      items.push({ label: post.title, to: undefined });
    }

    return items;
  };

  return {
    // State
    categories,
    currentCategory,
    posts,
    currentPost,
    relatedPosts,
    meta,
    isLoading,
    currentPage,

    // Actions
    fetchCategories,
    fetchCategoryBySlug,
    fetchPosts,
    fetchPostBySlug,
    fetchRelatedPosts,

    // Helpers
    buildBreadcrumbs,
  };
}
