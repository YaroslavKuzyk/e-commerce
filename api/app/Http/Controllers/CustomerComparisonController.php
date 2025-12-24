<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductVariantCatalogResource;
use App\Models\ProductVariant;
use App\Models\ProductCategory;
use App\Models\UserComparison;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerComparisonController extends Controller
{
    /**
     * Get comparison lists grouped by category.
     * Returns category info with variant count and preview images.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $comparisons = $user->comparisons()
            ->with(['category:id,name,slug,logo_file_id', 'productVariant.product:id,main_image_file_id', 'productVariant.images'])
            ->get();

        $grouped = $comparisons->groupBy('category_id')->map(function ($items, $categoryId) {
            $category = $items->first()->category;
            $variants = $items->pluck('productVariant')->filter();

            // Get preview images - prefer variant images, fall back to product main image
            $previewImages = $variants->take(4)->map(function ($variant) {
                $primaryImage = $variant->images->where('is_primary', true)->first();
                return $primaryImage?->file_id ?? $variant->product?->main_image_file_id;
            })->filter()->values()->toArray();

            return [
                'category' => $category ? [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'logo_file_id' => $category->logo_file_id,
                ] : null,
                'variants_count' => $items->count(),
                'variant_ids' => $items->pluck('product_variant_id')->toArray(),
                'preview_images' => $previewImages,
            ];
        })->filter(fn($item) => $item['category'] !== null)->values();

        return response()->json([
            'success' => true,
            'data' => $grouped,
        ]);
    }

    /**
     * Get product variants for comparison in a specific category.
     */
    public function showByCategory(Request $request, string $categorySlug): JsonResponse
    {
        $user = $request->user();

        $category = ProductCategory::where('slug', $categorySlug)->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $variantIds = $user->comparisons()
            ->where('category_id', $category->id)
            ->pluck('product_variant_id');

        $variants = ProductVariant::whereIn('id', $variantIds)
            ->where('status', 'published')
            ->with([
                'product.category.parent',
                'product.brand',
                'product.mainImage',
                'product.specifications',
                'attributeValues.attribute',
                'images.file',
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ],
                'variants' => ProductVariantCatalogResource::collection($variants),
            ],
        ]);
    }

    /**
     * Get all comparison variant IDs grouped by category.
     * For quick client-side checking.
     */
    public function ids(Request $request): JsonResponse
    {
        $user = $request->user();

        $comparisons = $user->comparisons()
            ->select('product_variant_id', 'category_id')
            ->get()
            ->groupBy('category_id')
            ->map(fn($items) => $items->pluck('product_variant_id')->toArray());

        return response()->json([
            'success' => true,
            'data' => $comparisons,
        ]);
    }

    /**
     * Add product variant to comparison.
     */
    public function store(Request $request, int $variantId): JsonResponse
    {
        $user = $request->user();

        $variant = ProductVariant::where('id', $variantId)
            ->where('status', 'published')
            ->with('product')
            ->first();

        if (!$variant || !$variant->product) {
            return response()->json([
                'success' => false,
                'message' => 'Product variant not found',
            ], 404);
        }

        if ($user->hasComparison($variantId)) {
            return response()->json([
                'success' => true,
                'message' => 'Product already in comparison',
                'is_compared' => true,
                'category_id' => $variant->product->category_id,
            ]);
        }

        UserComparison::create([
            'user_id' => $user->id,
            'product_variant_id' => $variantId,
            'category_id' => $variant->product->category_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product added to comparison',
            'is_compared' => true,
            'category_id' => $variant->product->category_id,
        ], 201);
    }

    /**
     * Remove product variant from comparison.
     */
    public function destroy(Request $request, int $variantId): JsonResponse
    {
        $user = $request->user();

        $user->comparisons()->where('product_variant_id', $variantId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product removed from comparison',
            'is_compared' => false,
        ]);
    }

    /**
     * Toggle product variant in comparison.
     */
    public function toggle(Request $request, int $variantId): JsonResponse
    {
        $user = $request->user();

        $variant = ProductVariant::where('id', $variantId)
            ->where('status', 'published')
            ->with('product')
            ->first();

        if (!$variant || !$variant->product) {
            return response()->json([
                'success' => false,
                'message' => 'Product variant not found',
            ], 404);
        }

        $isCompared = $user->hasComparison($variantId);

        if ($isCompared) {
            $user->comparisons()->where('product_variant_id', $variantId)->delete();
            $message = 'Product removed from comparison';
        } else {
            UserComparison::create([
                'user_id' => $user->id,
                'product_variant_id' => $variantId,
                'category_id' => $variant->product->category_id,
            ]);
            $message = 'Product added to comparison';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_compared' => !$isCompared,
            'category_id' => $variant->product->category_id,
        ]);
    }

    /**
     * Sync comparisons from localStorage.
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.variant_id' => 'required|integer',
            'items.*.category_id' => 'required|integer',
        ]);

        $user = $request->user();
        $items = $request->input('items');

        $variantIds = array_column($items, 'variant_id');

        // Validate variants exist and are published, get their product's category
        $validVariants = ProductVariant::whereIn('id', $variantIds)
            ->where('status', 'published')
            ->with('product:id,category_id')
            ->get()
            ->mapWithKeys(fn($v) => [$v->id => $v->product?->category_id])
            ->filter()
            ->toArray();

        $toInsert = [];
        foreach ($items as $item) {
            if (isset($validVariants[$item['variant_id']])) {
                // Use actual product category, not the one from localStorage
                $toInsert[] = [
                    'user_id' => $user->id,
                    'product_variant_id' => $item['variant_id'],
                    'category_id' => $validVariants[$item['variant_id']],
                    'created_at' => now(),
                ];
            }
        }

        if (!empty($toInsert)) {
            // Use upsert to skip duplicates
            UserComparison::upsert(
                $toInsert,
                ['user_id', 'product_variant_id'],
                ['category_id']
            );
        }

        // Return updated comparisons
        $comparisons = $user->comparisons()
            ->select('product_variant_id', 'category_id')
            ->get()
            ->groupBy('category_id')
            ->map(fn($items) => $items->pluck('product_variant_id')->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Comparisons synced successfully',
            'data' => $comparisons,
        ]);
    }

    /**
     * Clear all comparisons for a category.
     */
    public function clearCategory(Request $request, string $categorySlug): JsonResponse
    {
        $user = $request->user();

        $category = ProductCategory::where('slug', $categorySlug)->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $user->comparisons()->where('category_id', $category->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comparison list cleared',
        ]);
    }

    /**
     * Check if variants are in comparison (bulk check).
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'variant_ids' => 'required|array',
            'variant_ids.*' => 'integer',
        ]);

        $user = $request->user();
        $variantIds = $request->input('variant_ids');

        $comparedIds = $user->comparisons()
            ->whereIn('product_variant_id', $variantIds)
            ->pluck('product_variant_id')
            ->toArray();

        $result = [];
        foreach ($variantIds as $id) {
            $result[$id] = in_array($id, $comparedIds);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
