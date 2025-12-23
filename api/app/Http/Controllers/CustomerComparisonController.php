<?php

namespace App\Http\Controllers;

use App\Models\UserComparison;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerComparisonController extends Controller
{
    /**
     * Get all comparison lists grouped by category.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $comparisons = UserComparison::where('user_id', $user->id)
            ->with(['product.brand', 'product.specifications', 'category'])
            ->get();

        // Group by category
        $grouped = $comparisons->groupBy('category_id')->map(function ($items, $categoryId) {
            $category = $items->first()->category;
            return [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ],
                'products_count' => $items->count(),
                'products' => $items->map(fn($item) => $item->product)->values(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $grouped,
            'meta' => [
                'total_categories' => $grouped->count(),
                'total_products' => $comparisons->count(),
            ],
        ]);
    }

    /**
     * Get comparison summary (product IDs grouped by category).
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        $comparisons = UserComparison::where('user_id', $user->id)
            ->select('product_id', 'category_id')
            ->get();

        // Group product IDs by category
        $grouped = $comparisons->groupBy('category_id')->map(function ($items) {
            return $items->pluck('product_id')->values();
        });

        return response()->json([
            'success' => true,
            'data' => $grouped,
            'meta' => [
                'total_categories' => $grouped->count(),
                'total_products' => $comparisons->count(),
            ],
        ]);
    }

    /**
     * Get products for comparison by category ID.
     */
    public function byCategory(Request $request, int $categoryId): JsonResponse
    {
        $user = $request->user();

        $comparisons = UserComparison::where('user_id', $user->id)
            ->where('category_id', $categoryId)
            ->with(['product.brand', 'product.variants.attributeValues.attribute', 'product.category.parent.parent', 'product.mainImage', 'product.specifications', 'category'])
            ->get();

        $category = $comparisons->first()?->category;

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category ? [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ] : null,
                'products' => $comparisons->map(fn($c) => $c->product)->values(),
            ],
        ]);
    }

    /**
     * Get products for comparison by category slug.
     */
    public function byCategorySlug(Request $request, string $slug): JsonResponse
    {
        $user = $request->user();

        // Find root category by slug
        $category = \App\Models\ProductCategory::where('slug', $slug)
            ->whereNull('parent_id')
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $comparisons = UserComparison::where('user_id', $user->id)
            ->where('category_id', $category->id)
            ->with(['product.brand', 'product.variants.attributeValues.attribute', 'product.category.parent.parent', 'product.mainImage', 'product.specifications'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ],
                'products' => $comparisons->map(fn($c) => $c->product)->values(),
            ],
        ]);
    }

    /**
     * Add product to comparison.
     */
    public function store(Request $request, int $productId): JsonResponse
    {
        $user = $request->user();

        $product = Product::findOrFail($productId);

        // Check if product already in comparison
        $exists = UserComparison::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Product already in comparison',
            ], 409);
        }

        // Get the root category for comparison grouping
        $categoryId = $this->getRootCategoryId($product);

        UserComparison::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'category_id' => $categoryId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product added to comparison',
            'data' => [
                'product_id' => $productId,
                'category_id' => $categoryId,
            ],
        ], 201);
    }

    /**
     * Remove product from comparison.
     */
    public function destroy(Request $request, int $productId): JsonResponse
    {
        $user = $request->user();

        $deleted = UserComparison::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found in comparison',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product removed from comparison',
        ]);
    }

    /**
     * Toggle product in comparison.
     */
    public function toggle(Request $request, int $productId): JsonResponse
    {
        $user = $request->user();

        $existing = UserComparison::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'success' => true,
                'message' => 'Product removed from comparison',
                'data' => [
                    'added' => false,
                    'product_id' => $productId,
                ],
            ]);
        }

        $product = Product::findOrFail($productId);
        $categoryId = $this->getRootCategoryId($product);

        UserComparison::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'category_id' => $categoryId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product added to comparison',
            'data' => [
                'added' => true,
                'product_id' => $productId,
                'category_id' => $categoryId,
            ],
        ]);
    }

    /**
     * Sync comparisons from localStorage (after login).
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer|exists:products,id',
        ]);

        $user = $request->user();

        foreach ($request->items as $item) {
            $productId = $item['product_id'];

            $exists = UserComparison::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->exists();

            if (!$exists) {
                $product = Product::find($productId);
                if ($product) {
                    $categoryId = $this->getRootCategoryId($product);
                    UserComparison::create([
                        'user_id' => $user->id,
                        'product_id' => $productId,
                        'category_id' => $categoryId,
                    ]);
                }
            }
        }

        // Return updated summary
        $comparisons = UserComparison::where('user_id', $user->id)
            ->select('product_id', 'category_id')
            ->get();

        $grouped = $comparisons->groupBy('category_id')->map(function ($items) {
            return $items->pluck('product_id')->values();
        });

        return response()->json([
            'success' => true,
            'message' => 'Comparisons synced successfully',
            'data' => $grouped,
            'meta' => [
                'total_categories' => $grouped->count(),
                'total_products' => $comparisons->count(),
            ],
        ]);
    }

    /**
     * Clear all comparisons or by category.
     */
    public function clear(Request $request): JsonResponse
    {
        $user = $request->user();
        $categoryId = $request->query('category_id');

        $query = UserComparison::where('user_id', $user->id);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $query->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comparisons cleared',
        ]);
    }

    /**
     * Get the root (top-level) category ID for a product.
     * Products in the same root category can be compared.
     */
    private function getRootCategoryId(Product $product): int
    {
        if (!$product->category_id) {
            return 0;
        }

        $category = $product->category;

        // Traverse up to find root category
        while ($category && $category->parent_id) {
            $category = $category->parent;
        }

        return $category ? $category->id : $product->category_id;
    }
}
