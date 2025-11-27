import { defineStore } from 'pinia';
import type { BlogPostFormData, BlogPostFilters } from '~/models/blogPost';

export const useBlogPostStore = defineStore('blogPost', () => {
  const {
    getAllBlogPosts,
    getAllBlogPostsPromise,
    getBlogPostById,
    createBlogPost,
    updateBlogPost,
    deleteBlogPost,
    generateSlug,
    syncProducts,
  } = useBlogPost();

  return {
    fetchBlogPosts: async (filters?: BlogPostFilters) => await getAllBlogPosts(filters),
    fetchBlogPostsPromise: (filters?: BlogPostFilters) => getAllBlogPostsPromise(filters),
    fetchBlogPostById: async (id: number) => await getBlogPostById(id),
    onCreateBlogPost: async (payload: BlogPostFormData) => await createBlogPost(payload),
    onUpdateBlogPost: async (id: number, payload: Partial<BlogPostFormData>) => await updateBlogPost(id, payload),
    onDeleteBlogPost: async (id: number) => await deleteBlogPost(id),
    onGenerateSlug: async (title: string) => await generateSlug(title),
    onSyncProducts: async (postId: number, productIds: number[]) => await syncProducts(postId, productIds),
  };
});
