<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductVariantCatalogResource;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerFavoriteController extends Controller
{
    /**
     * Get user's favorite product variants with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $page = (int) $request->query('page', 1);
        $limit = min((int) $request->query('limit', 15), 50);

        $favorites = $user->favoriteVariants()
            ->where('status', 'published')
            ->with([
                'product.category.parent',
                'product.brand',
                'product.mainImage',
                'attributeValues.attribute',
                'images.file',
            ])
            ->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => ProductVariantCatalogResource::collection($favorites),
            'meta' => [
                'current_page' => $favorites->currentPage(),
                'last_page' => $favorites->lastPage(),
                'per_page' => $favorites->perPage(),
                'total' => $favorites->total(),
            ],
        ]);
    }

    /**
     * Get list of favorite variant IDs (for quick checking).
     */
    public function ids(Request $request): JsonResponse
    {
        $user = $request->user();

        $ids = $user->favoriteVariants()->pluck('product_variants.id')->toArray();

        return response()->json([
            'success' => true,
            'data' => $ids,
        ]);
    }

    /**
     * Add product variant to favorites.
     */
    public function store(Request $request, int $variantId): JsonResponse
    {
        $user = $request->user();

        // Check if variant exists and is published
        $variant = ProductVariant::where('id', $variantId)
            ->where('status', 'published')
            ->first();

        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Product variant not found',
            ], 404);
        }

        // Check if already in favorites
        if ($user->hasFavorite($variantId)) {
            return response()->json([
                'success' => true,
                'message' => 'Product already in favorites',
                'is_favorite' => true,
            ]);
        }

        $user->favoriteVariants()->attach($variantId);

        return response()->json([
            'success' => true,
            'message' => 'Product added to favorites',
            'is_favorite' => true,
        ], 201);
    }

    /**
     * Remove product variant from favorites.
     */
    public function destroy(Request $request, int $variantId): JsonResponse
    {
        $user = $request->user();

        $user->favoriteVariants()->detach($variantId);

        return response()->json([
            'success' => true,
            'message' => 'Product removed from favorites',
            'is_favorite' => false,
        ]);
    }

    /**
     * Toggle product variant in favorites (add if not exists, remove if exists).
     */
    public function toggle(Request $request, int $variantId): JsonResponse
    {
        $user = $request->user();

        // Check if variant exists and is published
        $variant = ProductVariant::where('id', $variantId)
            ->where('status', 'published')
            ->first();

        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Product variant not found',
            ], 404);
        }

        $isFavorite = $user->hasFavorite($variantId);

        if ($isFavorite) {
            $user->favoriteVariants()->detach($variantId);
            $message = 'Product removed from favorites';
        } else {
            $user->favoriteVariants()->attach($variantId);
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
            'variant_ids' => 'required|array',
            'variant_ids.*' => 'integer',
        ]);

        $user = $request->user();
        $variantIds = $request->input('variant_ids');

        // Get only valid published variant IDs
        $validVariantIds = ProductVariant::whereIn('id', $variantIds)
            ->where('status', 'published')
            ->pluck('id')
            ->toArray();

        // Merge with existing favorites (syncWithoutDetaching won't remove existing)
        if (!empty($validVariantIds)) {
            $user->favoriteVariants()->syncWithoutDetaching($validVariantIds);
        }

        // Return updated list of favorite IDs
        $updatedIds = $user->favoriteVariants()->pluck('product_variants.id')->toArray();

        return response()->json([
            'success' => true,
            'message' => 'Favorites synced successfully',
            'data' => $updatedIds,
        ]);
    }

    /**
     * Check if variants are in favorites (bulk check).
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'variant_ids' => 'required|array',
            'variant_ids.*' => 'integer',
        ]);

        $user = $request->user();
        $variantIds = $request->input('variant_ids');

        $favoriteIds = $user->favoriteVariants()
            ->whereIn('product_variants.id', $variantIds)
            ->pluck('product_variants.id')
            ->toArray();

        // Return object with variant_id => is_favorite
        $result = [];
        foreach ($variantIds as $id) {
            $result[$id] = in_array($id, $favoriteIds);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
