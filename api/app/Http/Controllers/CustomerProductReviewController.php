<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductReviewImage;
use App\Http\Resources\ProductReviewResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerProductReviewController extends Controller
{
    /**
     * Get reviews for a product by slug.
     */
    public function index(Request $request, string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)->published()->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $page = (int) $request->query('page', 1);
        $limit = min((int) $request->query('limit', 10), 50);

        $reviews = ProductReview::where('product_id', $product->id)
            ->approved()
            ->with('images')
            ->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $page);

        // Calculate rating statistics
        $stats = ProductReview::where('product_id', $product->id)
            ->approved()
            ->selectRaw('
                COUNT(*) as total_reviews,
                AVG(rating) as average_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_5,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1
            ')
            ->first();

        return response()->json([
            'success' => true,
            'data' => ProductReviewResource::collection($reviews),
            'stats' => [
                'total_reviews' => (int) $stats->total_reviews,
                'average_rating' => $stats->average_rating ? round((float) $stats->average_rating, 2) : 0,
                'rating_distribution' => [
                    5 => (int) $stats->rating_5,
                    4 => (int) $stats->rating_4,
                    3 => (int) $stats->rating_3,
                    2 => (int) $stats->rating_2,
                    1 => (int) $stats->rating_1,
                ],
            ],
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    /**
     * Store a new review for a product.
     */
    public function store(Request $request, string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)->published()->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'author_name' => 'required|string|max:255',
            'author_email' => 'required|email|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'advantages' => 'nullable|string|max:2000',
            'disadvantages' => 'nullable|string|max:2000',
            'comment' => 'nullable|string|max:5000',
            'youtube_urls' => 'nullable|array|max:5',
            'youtube_urls.*' => ['url', 'regex:/^https?:\/\/(www\.)?(youtube\.com|youtu\.be)\/.+$/'],
            'image_ids' => 'nullable|array|max:10',
            'image_ids.*' => 'integer|exists:files,id',
            'notify_on_reply' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $review = ProductReview::create([
                'product_id' => $product->id,
                'user_id' => $request->user()?->id,
                'author_name' => $request->input('author_name'),
                'author_email' => $request->input('author_email'),
                'rating' => $request->input('rating'),
                'advantages' => $request->input('advantages'),
                'disadvantages' => $request->input('disadvantages'),
                'comment' => $request->input('comment'),
                'youtube_urls' => $request->input('youtube_urls'),
                'notify_on_reply' => $request->boolean('notify_on_reply', false),
                'status' => 'pending',
            ]);

            // Attach images
            $imageIds = $request->input('image_ids');
            if (!empty($imageIds) && is_array($imageIds)) {
                foreach ($imageIds as $index => $fileId) {
                    ProductReviewImage::create([
                        'review_id' => $review->id,
                        'file_id' => $fileId,
                        'sort_order' => $index,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully. It will be visible after moderation.',
                'data' => new ProductReviewResource($review->load('images')),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to submit review: ' . $e->getMessage(), [
                'exception' => $e,
                'product_slug' => $slug,
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get review statistics for a product by slug (without full review list).
     */
    public function stats(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)->published()->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $stats = ProductReview::where('product_id', $product->id)
            ->approved()
            ->selectRaw('
                COUNT(*) as total_reviews,
                AVG(rating) as average_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_5,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1
            ')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'total_reviews' => (int) $stats->total_reviews,
                'average_rating' => $stats->average_rating ? round((float) $stats->average_rating, 2) : 0,
                'rating_distribution' => [
                    5 => (int) $stats->rating_5,
                    4 => (int) $stats->rating_4,
                    3 => (int) $stats->rating_3,
                    2 => (int) $stats->rating_2,
                    1 => (int) $stats->rating_1,
                ],
            ],
        ]);
    }
}
