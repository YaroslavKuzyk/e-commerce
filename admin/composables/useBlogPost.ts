import { BlogPostService } from '~/services/BlogPostService';
import type { BlogPostFilters, BlogPostFormData } from '~/models/blogPost';

export const useBlogPost = () => {
  const provider = new BlogPostService();

  return {
    getAllBlogPosts: (filters?: BlogPostFilters) => provider.getAllBlogPosts(filters),
    getAllBlogPostsPromise: (filters?: BlogPostFilters) => provider.getAllBlogPostsPromise(filters),
    getBlogPostById: (id: number) => provider.getBlogPostById(id),
    createBlogPost: (payload: BlogPostFormData) => provider.createBlogPost(payload),
    updateBlogPost: (id: number, payload: Partial<BlogPostFormData>) => provider.updateBlogPost(id, payload),
    deleteBlogPost: (id: number) => provider.deleteBlogPost(id),
    generateSlug: (title: string) => provider.generateSlug(title),
    syncProducts: (postId: number, productIds: number[]) => provider.syncProducts(postId, productIds),
  };
};
