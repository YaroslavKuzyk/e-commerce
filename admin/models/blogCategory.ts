export type BlogCategoryStatus = 'draft' | 'published';

export interface BlogCategory {
  id: number;
  name: string;
  description: string;
  slug: string;
  status: BlogCategoryStatus;
  sort_order: number;
  posts_count?: number;
  created_at: string;
  updated_at: string;
}

export interface BlogCategoryFormData {
  name: string;
  description: string;
  slug: string;
  status: BlogCategoryStatus;
  sort_order: number;
}

export interface BlogCategoryFilters {
  name?: string;
  status?: BlogCategoryStatus;
  page?: number;
  per_page?: number;
}

export interface BlogCategoryPaginatedResponse {
  data: BlogCategory[];
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface IBlogCategoryProvider {
  getAllBlogCategories: (filters?: BlogCategoryFilters) => ReturnType<typeof useAsyncData<BlogCategoryPaginatedResponse>>;
  getBlogCategoryById: (id: number) => ReturnType<typeof useAsyncData<BlogCategory>>;
  createBlogCategory: (payload: BlogCategoryFormData) => ReturnType<typeof useAsyncData<BlogCategory>>;
  updateBlogCategory: (id: number, payload: Partial<BlogCategoryFormData>) => ReturnType<typeof useAsyncData<BlogCategory>>;
  deleteBlogCategory: (id: number) => ReturnType<typeof useAsyncData<{ success: boolean; message: string }>>;
  generateSlug: (name: string) => ReturnType<typeof useAsyncData<{ slug: string }>>;
  reorderCategories: (categoryIds: number[]) => ReturnType<typeof useAsyncData<BlogCategory[]>>;
}
