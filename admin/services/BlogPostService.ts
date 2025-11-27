import type {
  BlogPost,
  BlogPostFormData,
  BlogPostFilters,
  BlogPostPaginatedResponse,
  IBlogPostProvider,
} from '~/models/blogPost';

export class BlogPostService implements IBlogPostProvider {
  getAllBlogPosts(filters?: BlogPostFilters) {
    const client = useSanctumClient();

    const params = new URLSearchParams();
    if (filters?.title) params.append('title', filters.title);
    if (filters?.status) params.append('status', filters.status);
    if (filters?.blog_category_id) params.append('blog_category_id', filters.blog_category_id.toString());
    if (filters?.page) params.append('page', filters.page.toString());
    if (filters?.per_page) params.append('per_page', filters.per_page.toString());

    const queryString = params.toString();
    const url = `/api/admin/blog-posts${queryString ? `?${queryString}` : ''}`;

    return useAsyncData<BlogPostPaginatedResponse>(
      'blog-posts',
      async (): Promise<BlogPostPaginatedResponse> => {
        const res = await client<{ success: boolean; data: BlogPost[]; meta?: BlogPostPaginatedResponse['meta'] }>(url);
        return {
          data: res.data,
          meta: res.meta,
        };
      }
    );
  }

  async getAllBlogPostsPromise(filters?: BlogPostFilters): Promise<BlogPostPaginatedResponse> {
    const client = useSanctumClient();

    const params = new URLSearchParams();
    if (filters?.title) params.append('title', filters.title);
    if (filters?.status) params.append('status', filters.status);
    if (filters?.blog_category_id) params.append('blog_category_id', filters.blog_category_id.toString());
    if (filters?.page) params.append('page', filters.page.toString());
    if (filters?.per_page) params.append('per_page', filters.per_page.toString());

    const queryString = params.toString();
    const url = `/api/admin/blog-posts${queryString ? `?${queryString}` : ''}`;

    const res = await client<{ success: boolean; data: BlogPost[]; meta?: BlogPostPaginatedResponse['meta'] }>(url);
    return {
      data: res.data,
      meta: res.meta,
    };
  }

  getBlogPostById(id: number) {
    const client = useSanctumClient();

    return useAsyncData<BlogPost>(
      `blog-post-${id}`,
      () =>
        client<{ success: boolean; data: BlogPost }>(
          `/api/admin/blog-posts/${id}`
        ).then((res) => res.data)
    );
  }

  createBlogPost(payload: BlogPostFormData) {
    const client = useSanctumClient();

    return useAsyncData<BlogPost>(
      `blog-post-create-${Date.now()}`,
      () =>
        client<{ success: boolean; data: BlogPost }>(
          '/api/admin/blog-posts',
          {
            method: 'POST',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  updateBlogPost(id: number, payload: Partial<BlogPostFormData>) {
    const client = useSanctumClient();

    return useAsyncData<BlogPost>(
      `blog-post-update-${id}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: BlogPost }>(
          `/api/admin/blog-posts/${id}`,
          {
            method: 'PUT',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  deleteBlogPost(id: number) {
    const client = useSanctumClient();

    return useAsyncData<{ success: boolean; message: string }>(
      `blog-post-delete-${id}-${Date.now()}`,
      () =>
        client<{ success: boolean; message: string }>(
          `/api/admin/blog-posts/${id}`,
          {
            method: 'DELETE',
          }
        ).then((res) => res)
    );
  }

  generateSlug(title: string) {
    const client = useSanctumClient();

    return useAsyncData<{ slug: string }>(
      `blog-post-slug-${Date.now()}`,
      () =>
        client<{ success: boolean; data: { slug: string } }>(
          '/api/admin/blog-posts/generate-slug',
          {
            method: 'POST',
            body: { title },
          }
        ).then((res) => res.data)
    );
  }

  syncProducts(postId: number, productIds: number[]) {
    const client = useSanctumClient();

    return useAsyncData<BlogPost>(
      `blog-post-sync-products-${postId}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: BlogPost }>(
          `/api/admin/blog-posts/${postId}/products`,
          {
            method: 'POST',
            body: { product_ids: productIds },
          }
        ).then((res) => res.data)
    );
  }
}
