<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\Services\Admin\BlogPostServiceInterface;
use App\Http\Resources\BlogPostResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminBlogPostController extends Controller
{
    public function __construct(
        private BlogPostServiceInterface $adminBlogPostService
    ) {}

    /**
     * Display a listing of blog posts.
     *
     * @OA\Get(
     *     path="/api/admin/blog-posts",
     *     tags={"Admin Blog Posts"},
     *     summary="Get all blog posts",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="title", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"draft", "published"})),
     *     @OA\Parameter(name="blog_category_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of blog posts"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['title', 'status', 'blog_category_id', 'per_page', 'page']);
        $posts = $this->adminBlogPostService->getAllPosts($filters);

        if ($posts instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
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

        return response()->json([
            'success' => true,
            'data' => BlogPostResource::collection($posts),
        ]);
    }

    /**
     * Display the specified blog post.
     *
     * @OA\Get(
     *     path="/api/admin/blog-posts/{id}",
     *     tags={"Admin Blog Posts"},
     *     summary="Get blog post by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Blog post found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Blog post not found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $post = $this->adminBlogPostService->getPostById($id);

            return response()->json([
                'success' => true,
                'data' => new BlogPostResource($post),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Store a newly created blog post.
     *
     * @OA\Post(
     *     path="/api/admin/blog-posts",
     *     tags={"Admin Blog Posts"},
     *     summary="Create a new blog post",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "short_description", "slug", "content", "status", "blog_category_id"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="short_description", type="string"),
     *             @OA\Property(property="slug", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="preview_image_id", type="integer", nullable=true),
     *             @OA\Property(property="status", type="string", enum={"draft", "published"}),
     *             @OA\Property(property="publication_date", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="blog_category_id", type="integer"),
     *             @OA\Property(property="product_ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(response=201, description="Blog post created successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'short_description' => 'required|string',
            'slug' => 'required|string|unique:blog_posts,slug|max:255',
            'content' => 'required|string',
            'preview_image_id' => 'nullable|integer|exists:files,id',
            'status' => 'required|in:draft,published',
            'publication_date' => 'nullable|date',
            'blog_category_id' => 'required|integer|exists:blog_categories,id',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $post = $this->adminBlogPostService->createPost($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Blog post created successfully',
                'data' => new BlogPostResource($post),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified blog post.
     *
     * @OA\Put(
     *     path="/api/admin/blog-posts/{id}",
     *     tags={"Admin Blog Posts"},
     *     summary="Update blog post",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="short_description", type="string"),
     *             @OA\Property(property="slug", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="preview_image_id", type="integer", nullable=true),
     *             @OA\Property(property="status", type="string", enum={"draft", "published"}),
     *             @OA\Property(property="publication_date", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="blog_category_id", type="integer"),
     *             @OA\Property(property="product_ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Blog post updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Blog post not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'short_description' => 'sometimes|required|string',
            'slug' => 'sometimes|required|string|unique:blog_posts,slug,' . $id . '|max:255',
            'content' => 'sometimes|required|string',
            'preview_image_id' => 'nullable|integer|exists:files,id',
            'status' => 'sometimes|required|in:draft,published',
            'publication_date' => 'nullable|date',
            'blog_category_id' => 'sometimes|required|integer|exists:blog_categories,id',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $post = $this->adminBlogPostService->updatePost($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Blog post updated successfully',
                'data' => new BlogPostResource($post),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Remove the specified blog post.
     *
     * @OA\Delete(
     *     path="/api/admin/blog-posts/{id}",
     *     tags={"Admin Blog Posts"},
     *     summary="Delete blog post",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Blog post deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Blog post not found")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->adminBlogPostService->deletePost($id);

            return response()->json([
                'success' => true,
                'message' => 'Blog post deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Generate a unique slug.
     *
     * @OA\Post(
     *     path="/api/admin/blog-posts/generate-slug",
     *     tags={"Admin Blog Posts"},
     *     summary="Generate unique slug from title",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Slug generated successfully")
     * )
     */
    public function generateSlug(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $baseSlug = Str::slug($request->title);
        $slug = $baseSlug;
        $counter = 1;

        while (\App\Models\BlogPost::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return response()->json([
            'success' => true,
            'data' => ['slug' => $slug],
        ]);
    }

    /**
     * Sync products for a blog post.
     *
     * @OA\Post(
     *     path="/api/admin/blog-posts/{id}/products",
     *     tags={"Admin Blog Posts"},
     *     summary="Sync products for a blog post",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_ids"},
     *             @OA\Property(property="product_ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Products synced successfully")
     * )
     */
    public function syncProducts(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $post = $this->adminBlogPostService->syncProducts($id, $request->input('product_ids'));

            return response()->json([
                'success' => true,
                'message' => 'Products synced successfully',
                'data' => new BlogPostResource($post),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
