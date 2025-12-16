<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerFavoriteController extends Controller
{
    /**
     * Get user's favorite products with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $page = (int) $request->query('page', 1);
        $limit = min((int) $request->query('limit', 15), 50);

        $favorites = $user->favoriteProducts()
            ->published()
            ->with(['category', 'brand', 'mainImage', 'variants.images', 'attributes.values'])
            ->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($favorites),
            'meta' => [
                'current_page' => $favorites->currentPage(),
                'last_page' => $favorites->lastPage(),
                'per_page' => $favorites->perPage(),
                'total' => $favorites->total(),
            ],
        ]);
    }

    /**
     * Get list of favorite product IDs (for quick checking).
     */
    public function ids(Request $request): JsonResponse
    {
        $user = $request->user();

        $ids = $user->favoriteProducts()->pluck('products.id')->toArray();

        return response()->json([
            'success' => true,
            'data' => $ids,
        ]);
    }

    /**
     * Add product to favorites.
     */
    public function store(Request $request, int $productId): JsonResponse
    {
        $user = $request->user();

        // Check if product exists and is published
        $product = Product::where('id', $productId)->published()->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        // Check if already in favorites
        if ($user->hasFavorite($productId)) {
            return response()->json([
                'success' => true,
                'message' => 'Product already in favorites',
                'is_favorite' => true,
            ]);
        }

        $user->favoriteProducts()->attach($productId);

        return response()->json([
            'success' => true,
            'message' => 'Product added to favorites',
            'is_favorite' => true,
        ], 201);
    }

    /**
     * Remove product from favorites.
     */
    public function destroy(Request $request, int $productId): JsonResponse
    {
        $user = $request->user();

        $user->favoriteProducts()->detach($productId);

        return response()->json([
            'success' => true,
            'message' => 'Product removed from favorites',
            'is_favorite' => false,
        ]);
    }

    /**
     * Toggle product in favorites (add if not exists, remove if exists).
     */
    public function toggle(Request $request, int $productId): JsonResponse
    {
        $user = $request->user();

        // Check if product exists and is published
        $product = Product::where('id', $productId)->published()->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $isFavorite = $user->hasFavorite($productId);

        if ($isFavorite) {
            $user->favoriteProducts()->detach($productId);
            $message = 'Product removed from favorites';
        } else {
            $user->favoriteProducts()->attach($productId);
            $message = 'Product added to favorites';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_favorite' => !$isFavorite,
        ]);
    }

    /**
     * Sync favorites from localStorage (merge with existing).
     * Used when guest user logs in.
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer',
        ]);

        $user = $request->user();
        $productIds = $request->input('product_ids');

        // Get only valid published product IDs
        $validProductIds = Product::whereIn('id', $productIds)
            ->published()
            ->pluck('id')
            ->toArray();

        // Merge with existing favorites (syncWithoutDetaching won't remove existing)
        if (!empty($validProductIds)) {
            $user->favoriteProducts()->syncWithoutDetaching($validProductIds);
        }

        // Return updated list of favorite IDs
        $updatedIds = $user->favoriteProducts()->pluck('products.id')->toArray();

        return response()->json([
            'success' => true,
            'message' => 'Favorites synced successfully',
            'data' => $updatedIds,
        ]);
    }

    /**
     * Check if products are in favorites (bulk check).
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer',
        ]);

        $user = $request->user();
        $productIds = $request->input('product_ids');

        $favoriteIds = $user->favoriteProducts()
            ->whereIn('products.id', $productIds)
            ->pluck('products.id')
            ->toArray();

        // Return object with product_id => is_favorite
        $result = [];
        foreach ($productIds as $id) {
            $result[$id] = in_array($id, $favoriteIds);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
