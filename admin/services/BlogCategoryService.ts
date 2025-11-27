import type {
  BlogCategory,
  BlogCategoryFormData,
  BlogCategoryFilters,
  BlogCategoryPaginatedResponse,
  IBlogCategoryProvider,
} from '~/models/blogCategory';

export class BlogCategoryService implements IBlogCategoryProvider {
  getAllBlogCategories(filters?: BlogCategoryFilters) {
    const client = useSanctumClient();

    const params = new URLSearchParams();
    if (filters?.name) params.append('name', filters.name);
    if (filters?.status) params.append('status', filters.status);
    if (filters?.page) params.append('page', filters.page.toString());
    if (filters?.per_page) params.append('per_page', filters.per_page.toString());

    const queryString = params.toString();
    const url = `/api/admin/blog-categories${queryString ? `?${queryString}` : ''}`;

    return useAsyncData<BlogCategoryPaginatedResponse>(
      'blog-categories',
      async (): Promise<BlogCategoryPaginatedResponse> => {
        const res = await client<{ success: boolean; data: BlogCategory[]; meta?: BlogCategoryPaginatedResponse['meta'] }>(url);
        return {
          data: res.data,
          meta: res.meta,
        };
      }
    );
  }

  async getAllBlogCategoriesPromise(filters?: BlogCategoryFilters): Promise<BlogCategoryPaginatedResponse> {
    const client = useSanctumClient();

    const params = new URLSearchParams();
    if (filters?.name) params.append('name', filters.name);
    if (filters?.status) params.append('status', filters.status);
    if (filters?.page) params.append('page', filters.page.toString());
    if (filters?.per_page) params.append('per_page', filters.per_page.toString());

    const queryString = params.toString();
    const url = `/api/admin/blog-categories${queryString ? `?${queryString}` : ''}`;

    const res = await client<{ success: boolean; data: BlogCategory[]; meta?: BlogCategoryPaginatedResponse['meta'] }>(url);
    return {
      data: res.data,
      meta: res.meta,
    };
  }

  getBlogCategoryById(id: number) {
    const client = useSanctumClient();

    return useAsyncData<BlogCategory>(
      `blog-category-${id}`,
      () =>
        client<{ success: boolean; data: BlogCategory }>(
          `/api/admin/blog-categories/${id}`
        ).then((res) => res.data)
    );
  }

  createBlogCategory(payload: BlogCategoryFormData) {
    const client = useSanctumClient();

    return useAsyncData<BlogCategory>(
      `blog-category-create-${Date.now()}`,
      () =>
        client<{ success: boolean; data: BlogCategory }>(
          '/api/admin/blog-categories',
          {
            method: 'POST',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  updateBlogCategory(id: number, payload: Partial<BlogCategoryFormData>) {
    const client = useSanctumClient();

    return useAsyncData<BlogCategory>(
      `blog-category-update-${id}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: BlogCategory }>(
          `/api/admin/blog-categories/${id}`,
          {
            method: 'PUT',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  deleteBlogCategory(id: number) {
    const client = useSanctumClient();

    return useAsyncData<{ success: boolean; message: string }>(
      `blog-category-delete-${id}-${Date.now()}`,
      () =>
        client<{ success: boolean; message: string }>(
          `/api/admin/blog-categories/${id}`,
          {
            method: 'DELETE',
          }
        ).then((res) => res)
    );
  }

  generateSlug(name: string) {
    const client = useSanctumClient();

    return useAsyncData<{ slug: string }>(
      `blog-category-slug-${Date.now()}`,
      () =>
        client<{ success: boolean; data: { slug: string } }>(
          '/api/admin/blog-categories/generate-slug',
          {
            method: 'POST',
            body: { name },
          }
        ).then((res) => res.data)
    );
  }

  reorderCategories(categoryIds: number[]) {
    const client = useSanctumClient();

    return useAsyncData<BlogCategory[]>(
      `blog-categories-reorder-${Date.now()}`,
      () =>
        client<{ success: boolean; data: BlogCategory[] }>(
          '/api/admin/blog-categories/reorder',
          {
            method: 'POST',
            body: { category_ids: categoryIds },
          }
        ).then((res) => res.data)
    );
  }
}
