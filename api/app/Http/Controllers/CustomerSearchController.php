<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerSearchController extends Controller
{
    /**
     * Global search across products, categories, blog categories, and blog posts
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q', '');
        $limit = min((int) $request->query('limit', 5), 10);

        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => [
                    'products' => [],
                    'categories' => [],
                    'blog_categories' => [],
                    'blog_posts' => [],
                ],
            ]);
        }

        $searchTerm = '%' . $query . '%';

        // Search products
        $products = Product::query()
            ->published()
            ->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('short_description', 'like', $searchTerm);
            })
            ->with(['category:id,name,slug', 'brand:id,name,slug'])
            ->select(['id', 'name', 'slug', 'base_price', 'discount_price', 'discount_percent', 'is_clearance', 'clearance_price', 'main_image_file_id', 'category_id', 'brand_id'])
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->current_price,
                    'base_price' => (float) $product->base_price,
                    'main_image_file_id' => $product->main_image_file_id,
                    'category' => $product->category ? [
                        'name' => $product->category->name,
                        'slug' => $product->category->slug,
                    ] : null,
                    'brand' => $product->brand ? [
                        'name' => $product->brand->name,
                        'slug' => $product->brand->slug,
                    ] : null,
                ];
            });

        // Search product categories (both parent and subcategories)
        $categories = ProductCategory::query()
            ->published()
            ->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('subtitle', 'like', $searchTerm);
            })
            ->with(['parent:id,name,slug'])
            ->select(['id', 'name', 'slug', 'subtitle', 'parent_id', 'logo_file_id'])
            ->limit($limit)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'subtitle' => $category->subtitle,
                    'logo_file_id' => $category->logo_file_id,
                    'is_subcategory' => $category->parent_id !== null,
                    'parent' => $category->parent ? [
                        'name' => $category->parent->name,
                        'slug' => $category->parent->slug,
                    ] : null,
                ];
            });

        // Search blog categories
        $blogCategories = BlogCategory::query()
            ->published()
            ->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            })
            ->select(['id', 'name', 'slug', 'description'])
            ->limit($limit)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                ];
            });

        // Search blog posts
        $blogPosts = BlogPost::query()
            ->published()
            ->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('short_description', 'like', $searchTerm);
            })
            ->with(['category:id,name,slug'])
            ->select(['id', 'title', 'slug', 'short_description', 'preview_image_id', 'blog_category_id', 'publication_date'])
            ->limit($limit)
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'short_description' => $post->short_description,
                    'preview_image_id' => $post->preview_image_id,
                    'publication_date' => $post->publication_date?->toISOString(),
                    'category' => $post->category ? [
                        'name' => $post->category->name,
                        'slug' => $post->category->slug,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products,
                'categories' => $categories,
                'blog_categories' => $blogCategories,
                'blog_posts' => $blogPosts,
            ],
        ]);
    }
}
