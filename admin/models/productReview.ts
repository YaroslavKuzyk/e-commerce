export type ReviewStatus = 'pending' | 'approved' | 'rejected';

export interface ProductReview {
  id: number;
  product_id: number;
  product?: {
    id: number;
    name: string;
    slug: string;
    main_image_file_id: number | null;
  };
  author_name: string;
  author_email: string;
  rating: number;
  advantages: string | null;
  disadvantages: string | null;
  comment: string | null;
  youtube_urls: string[];
  images?: {
    id: number;
    file_id: number;
  }[];
  status: ReviewStatus;
  notify_on_reply: boolean;
  created_at: string;
  updated_at: string;
}

export interface ProductReviewFilters {
  status?: ReviewStatus;
  product_id?: number;
  rating?: number;
  search?: string;
  page?: number;
  per_page?: number;
}

export interface ProductReviewStats {
  total: number;
  pending: number;
  approved: number;
  rejected: number;
}

export interface ProductReviewPaginatedResponse {
  data: ProductReview[];
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface IProductReviewProvider {
  getAllProductReviews: (filters?: ProductReviewFilters) => ReturnType<typeof useAsyncData<ProductReviewPaginatedResponse>>;
  getAllProductReviewsPromise: (filters?: ProductReviewFilters) => Promise<ProductReviewPaginatedResponse>;
  getProductReviewById: (id: number) => ReturnType<typeof useAsyncData<ProductReview>>;
  getProductReviewStats: () => ReturnType<typeof useAsyncData<ProductReviewStats>>;
  approveReview: (id: number) => ReturnType<typeof useAsyncData<ProductReview>>;
  rejectReview: (id: number) => ReturnType<typeof useAsyncData<ProductReview>>;
  deleteReview: (id: number) => ReturnType<typeof useAsyncData<{ success: boolean; message: string }>>;
  bulkApprove: (ids: number[]) => ReturnType<typeof useAsyncData<{ success: boolean; message: string }>>;
  bulkReject: (ids: number[]) => ReturnType<typeof useAsyncData<{ success: boolean; message: string }>>;
  bulkDelete: (ids: number[]) => ReturnType<typeof useAsyncData<{ success: boolean; message: string }>>;
}
