<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerPublicComparisonController extends Controller
{
    /**
     * Get comparison products by IDs (for shared links and guest users).
     * Public endpoint - no auth required.
     */
    public function getByIds(Request $request): JsonResponse
    {
        $request->validate([
            'product_ids' => 'required|array|max:50',
            'product_ids.*' => 'integer',
        ]);

        $productIds = $request->input('product_ids');

        $products = Product::whereIn('id', $productIds)
            ->published()
            ->with([
                'category',
                'brand',
                'mainImage',
                'variants.images',
                'variants.attributeValues',
                'attributes.values',
                'specifications',
            ])
            ->get();

        // Group by category
        $grouped = $products->groupBy('category_id')->map(function ($products, $categoryId) {
            $category = $products->first()->category;
            return [
                'category' => $category ? [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ] : null,
                'products' => ProductResource::collection($products),
            ];
        })->filter(fn($item) => $item['category'] !== null)->values();

        return response()->json([
            'success' => true,
            'data' => $grouped,
        ]);
    }
}
