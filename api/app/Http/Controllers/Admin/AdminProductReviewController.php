<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use App\Http\Resources\ProductReviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminProductReviewController extends Controller
{
    /**
     * Display a listing of product reviews.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductReview::with(['product', 'images']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by product
        if ($request->has('product_id') && $request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by rating
        if ($request->has('rating') && $request->rating) {
            $query->where('rating', $request->rating);
        }

        // Search by author
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('author_name', 'like', "%{$search}%")
                  ->orWhere('author_email', 'like', "%{$search}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        $perPage = min((int) $request->query('per_page', 15), 100);
        $reviews = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ProductReviewResource::collection($reviews),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    /**
     * Display the specified review.
     */
    public function show(int $id): JsonResponse
    {
        $review = ProductReview::with(['product', 'images'])->find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ProductReviewResource($review),
        ]);
    }

    /**
     * Approve a review.
     */
    public function approve(int $id): JsonResponse
    {
        $review = ProductReview::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found',
            ], 404);
        }

        $review->update(['status' => 'approved']);

        return response()->json([
            'success' => true,
            'message' => 'Review approved successfully',
            'data' => new ProductReviewResource($review->fresh()),
        ]);
    }

    /**
     * Reject a review.
     */
    public function reject(int $id): JsonResponse
    {
        $review = ProductReview::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found',
            ], 404);
        }

        $review->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Review rejected successfully',
            'data' => new ProductReviewResource($review->fresh()),
        ]);
    }

    /**
     * Delete a review.
     */
    public function destroy(int $id): JsonResponse
    {
        $review = ProductReview::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found',
            ], 404);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }

    /**
     * Get review statistics.
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total' => ProductReview::count(),
            'pending' => ProductReview::where('status', 'pending')->count(),
            'approved' => ProductReview::where('status', 'approved')->count(),
            'rejected' => ProductReview::where('status', 'rejected')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Bulk approve reviews.
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No review IDs provided',
            ], 400);
        }

        ProductReview::whereIn('id', $ids)->update(['status' => 'approved']);

        return response()->json([
            'success' => true,
            'message' => count($ids) . ' reviews approved successfully',
        ]);
    }

    /**
     * Bulk reject reviews.
     */
    public function bulkReject(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No review IDs provided',
            ], 400);
        }

        ProductReview::whereIn('id', $ids)->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => count($ids) . ' reviews rejected successfully',
        ]);
    }

    /**
     * Bulk delete reviews.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No review IDs provided',
            ], 400);
        }

        ProductReview::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => count($ids) . ' reviews deleted successfully',
        ]);
    }
}
