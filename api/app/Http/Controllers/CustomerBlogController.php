<?php

namespace App\Http\Controllers;

use App\Http\Resources\BlogCategoryResource;
use App\Http\Resources\BlogPostResource;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerBlogController extends Controller
{
    /**
     * Get all published blog categories with posts count.
     */
    public function categories(): JsonResponse
    {
        $categories = BlogCategory::published()
            ->ordered()
            ->withCount(['posts' => function ($query) {
                $query->published();
            }])
            ->get();

        return response()->json([
            'success' => true,
            'data' => BlogCategoryResource::collection($categories),
        ]);
    }

    /**
     * Get a single category by slug.
     */
    public function categoryBySlug(string $slug): JsonResponse
    {
        $category = BlogCategory::where('slug', $slug)
            ->published()
            ->withCount(['posts' => function ($query) {
                $query->published();
            }])
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new BlogCategoryResource($category),
        ]);
    }

    /**
     * Get paginated blog posts with optional category filter.
     */
    public function posts(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 12);
        $categorySlug = $request->query('category_slug');

        $query = BlogPost::published()
            ->with(['category', 'previewImage'])
            ->orderBy('publication_date', 'desc');

        if ($categorySlug) {
            $category = BlogCategory::where('slug', $categorySlug)->published()->first();
            if ($category) {
                $query->where('blog_category_id', $category->id);
            }
        }

        $posts = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => BlogPostResource::collection($posts),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    /**
     * Get a single post by slug.
     */
    public function postBySlug(string $slug): JsonResponse
    {
        $post = BlogPost::where('slug', $slug)
            ->published()
            ->with(['category', 'previewImage', 'products.mainImage', 'products.category'])
            ->first();

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new BlogPostResource($post),
        ]);
    }

    /**
     * Get related posts (same category, excluding current).
     */
    public function relatedPosts(string $slug, Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 4);

        $currentPost = BlogPost::where('slug', $slug)->published()->first();

        if (!$currentPost) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        $relatedPosts = BlogPost::published()
            ->where('id', '!=', $currentPost->id)
            ->where('blog_category_id', $currentPost->blog_category_id)
            ->with(['category', 'previewImage'])
            ->orderBy('publication_date', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => BlogPostResource::collection($relatedPosts),
        ]);
    }
}
