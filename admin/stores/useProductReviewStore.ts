import { defineStore } from 'pinia';
import type { ProductReviewFilters } from '~/models/productReview';

export const useProductReviewStore = defineStore('productReview', () => {
  const {
    getAllProductReviews,
    getAllProductReviewsPromise,
    getProductReviewById,
    getProductReviewStats,
    approveReview,
    rejectReview,
    deleteReview,
    bulkApprove,
    bulkReject,
    bulkDelete,
  } = useProductReview();

  return {
    fetchProductReviews: async (filters?: ProductReviewFilters) => await getAllProductReviews(filters),
    fetchProductReviewsPromise: (filters?: ProductReviewFilters) => getAllProductReviewsPromise(filters),
    fetchProductReviewById: async (id: number) => await getProductReviewById(id),
    fetchProductReviewStats: async () => await getProductReviewStats(),
    onApproveReview: async (id: number) => await approveReview(id),
    onRejectReview: async (id: number) => await rejectReview(id),
    onDeleteReview: async (id: number) => await deleteReview(id),
    onBulkApprove: async (ids: number[]) => await bulkApprove(ids),
    onBulkReject: async (ids: number[]) => await bulkReject(ids),
    onBulkDelete: async (ids: number[]) => await bulkDelete(ids),
  };
});
