import type {
  ProductReview,
  ProductReviewFilters,
  ProductReviewPaginatedResponse,
  ProductReviewStats,
  IProductReviewProvider,
} from '~/models/productReview';

export class ProductReviewService implements IProductReviewProvider {
  getAllProductReviews(filters?: ProductReviewFilters) {
    const client = useSanctumClient();

    const params = new URLSearchParams();
    if (filters?.status) params.append('status', filters.status);
    if (filters?.product_id) params.append('product_id', filters.product_id.toString());
    if (filters?.rating) params.append('rating', filters.rating.toString());
    if (filters?.search) params.append('search', filters.search);
    if (filters?.page) params.append('page', filters.page.toString());
    if (filters?.per_page) params.append('per_page', filters.per_page.toString());

    const queryString = params.toString();
    const url = `/api/admin/product-reviews${queryString ? `?${queryString}` : ''}`;

    return useAsyncData<ProductReviewPaginatedResponse>(
      'product-reviews',
      async (): Promise<ProductReviewPaginatedResponse> => {
        const res = await client<{ success: boolean; data: ProductReview[]; meta?: ProductReviewPaginatedResponse['meta'] }>(url);
        return {
          data: res.data,
          meta: res.meta,
        };
      }
    );
  }

  async getAllProductReviewsPromise(filters?: ProductReviewFilters): Promise<ProductReviewPaginatedResponse> {
    const client = useSanctumClient();

    const params = new URLSearchParams();
    if (filters?.status) params.append('status', filters.status);
    if (filters?.product_id) params.append('product_id', filters.product_id.toString());
    if (filters?.rating) params.append('rating', filters.rating.toString());
    if (filters?.search) params.append('search', filters.search);
    if (filters?.page) params.append('page', filters.page.toString());
    if (filters?.per_page) params.append('per_page', filters.per_page.toString());

    const queryString = params.toString();
    const url = `/api/admin/product-reviews${queryString ? `?${queryString}` : ''}`;

    const res = await client<{ success: boolean; data: ProductReview[]; meta?: ProductReviewPaginatedResponse['meta'] }>(url);
    return {
      data: res.data,
      meta: res.meta,
    };
  }

  getProductReviewById(id: number) {
    const client = useSanctumClient();

    return useAsyncData<ProductReview>(
      `product-review-${id}`,
      () =>
        client<{ success: boolean; data: ProductReview }>(
          `/api/admin/product-reviews/${id}`
        ).then((res) => res.data)
    );
  }

  getProductReviewStats() {
    const client = useSanctumClient();

    return useAsyncData<ProductReviewStats>(
      'product-review-stats',
      () =>
        client<{ success: boolean; data: ProductReviewStats }>(
          '/api/admin/product-reviews/stats'
        ).then((res) => res.data)
    );
  }

  approveReview(id: number) {
    const client = useSanctumClient();

    return useAsyncData<ProductReview>(
      `product-review-approve-${id}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: ProductReview }>(
          `/api/admin/product-reviews/${id}/approve`,
          {
            method: 'PATCH',
          }
        ).then((res) => res.data)
    );
  }

  rejectReview(id: number) {
    const client = useSanctumClient();

    return useAsyncData<ProductReview>(
      `product-review-reject-${id}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: ProductReview }>(
          `/api/admin/product-reviews/${id}/reject`,
          {
            method: 'PATCH',
          }
        ).then((res) => res.data)
    );
  }

  deleteReview(id: number) {
    const client = useSanctumClient();

    return useAsyncData<{ success: boolean; message: string }>(
      `product-review-delete-${id}-${Date.now()}`,
      () =>
        client<{ success: boolean; message: string }>(
          `/api/admin/product-reviews/${id}`,
          {
            method: 'DELETE',
          }
        ).then((res) => res)
    );
  }

  bulkApprove(ids: number[]) {
    const client = useSanctumClient();

    return useAsyncData<{ success: boolean; message: string }>(
      `product-review-bulk-approve-${Date.now()}`,
      () =>
        client<{ success: boolean; message: string }>(
          '/api/admin/product-reviews/bulk-approve',
          {
            method: 'POST',
            body: { ids },
          }
        ).then((res) => res)
    );
  }

  bulkReject(ids: number[]) {
    const client = useSanctumClient();

    return useAsyncData<{ success: boolean; message: string }>(
      `product-review-bulk-reject-${Date.now()}`,
      () =>
        client<{ success: boolean; message: string }>(
          '/api/admin/product-reviews/bulk-reject',
          {
            method: 'POST',
            body: { ids },
          }
        ).then((res) => res)
    );
  }

  bulkDelete(ids: number[]) {
    const client = useSanctumClient();

    return useAsyncData<{ success: boolean; message: string }>(
      `product-review-bulk-delete-${Date.now()}`,
      () =>
        client<{ success: boolean; message: string }>(
          '/api/admin/product-reviews/bulk-delete',
          {
            method: 'POST',
            body: { ids },
          }
        ).then((res) => res)
    );
  }
}
