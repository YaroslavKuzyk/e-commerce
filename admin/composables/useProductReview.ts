import { ProductReviewService } from '~/services/ProductReviewService';
import type { ProductReviewFilters } from '~/models/productReview';

export const useProductReview = () => {
  const provider = new ProductReviewService();

  return {
    getAllProductReviews: (filters?: ProductReviewFilters) => provider.getAllProductReviews(filters),
    getAllProductReviewsPromise: (filters?: ProductReviewFilters) => provider.getAllProductReviewsPromise(filters),
    getProductReviewById: (id: number) => provider.getProductReviewById(id),
    getProductReviewStats: () => provider.getProductReviewStats(),
    approveReview: (id: number) => provider.approveReview(id),
    rejectReview: (id: number) => provider.rejectReview(id),
    deleteReview: (id: number) => provider.deleteReview(id),
    bulkApprove: (ids: number[]) => provider.bulkApprove(ids),
    bulkReject: (ids: number[]) => provider.bulkReject(ids),
    bulkDelete: (ids: number[]) => provider.bulkDelete(ids),
  };
};
