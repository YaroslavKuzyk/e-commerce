import type { BlogCategory } from './blogCategory';
import type { Product } from './product';

export type BlogPostStatus = 'draft' | 'published';

export interface BlogPost {
  id: number;
  title: string;
  short_description: string;
  slug: string;
  content: string;
  preview_image_id: number | null;
  preview_image?: {
    id: number;
    filename: string;
    url: string;
  } | null;
  status: BlogPostStatus;
  publication_date: string | null;
  blog_category_id: number;
  category?: BlogCategory;
  products?: Product[];
  created_at: string;
  updated_at: string;
}

export interface BlogPostFormData {
  title: string;
  short_description: string;
  slug: string;
  content: string;
  preview_image_id?: number | null;
  status: BlogPostStatus;
  publication_date?: string | null;
  blog_category_id: number;
  product_ids?: number[];
}

export interface BlogPostFilters {
  title?: string;
  status?: BlogPostStatus;
  blog_category_id?: number;
  page?: number;
  per_page?: number;
}

export interface BlogPostPaginatedResponse {
  data: BlogPost[];
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface IBlogPostProvider {
  getAllBlogPosts: (filters?: BlogPostFilters) => ReturnType<typeof useAsyncData<BlogPostPaginatedResponse>>;
  getBlogPostById: (id: number) => ReturnType<typeof useAsyncData<BlogPost>>;
  createBlogPost: (payload: BlogPostFormData) => ReturnType<typeof useAsyncData<BlogPost>>;
  updateBlogPost: (id: number, payload: Partial<BlogPostFormData>) => ReturnType<typeof useAsyncData<BlogPost>>;
  deleteBlogPost: (id: number) => ReturnType<typeof useAsyncData<{ success: boolean; message: string }>>;
  generateSlug: (title: string) => ReturnType<typeof useAsyncData<{ slug: string }>>;
  syncProducts: (postId: number, productIds: number[]) => ReturnType<typeof useAsyncData<BlogPost>>;
}
