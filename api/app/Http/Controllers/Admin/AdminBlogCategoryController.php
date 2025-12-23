<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\Services\Admin\BlogCategoryServiceInterface;
use App\Http\Resources\BlogCategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminBlogCategoryController extends Controller
{
    public function __construct(
        private BlogCategoryServiceInterface $adminBlogCategoryService
    ) {}

    /**
     * Display a listing of blog categories.
     *
     * @OA\Get(
     *     path="/api/admin/blog-categories",
     *     tags={"Admin Blog Categories"},
     *     summary="Get all blog categories",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="name", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"draft", "published"})),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of blog categories"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['name', 'status', 'per_page', 'page']);
        $categories = $this->adminBlogCategoryService->getAllCategories($filters);

        if ($categories instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            return response()->json([
                'success' => true,
                'data' => BlogCategoryResource::collection($categories),
                'meta' => [
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'per_page' => $categories->perPage(),
                    'total' => $categories->total(),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => BlogCategoryResource::collection($categories),
        ]);
    }

    /**
     * Display the specified blog category.
     *
     * @OA\Get(
     *     path="/api/admin/blog-categories/{id}",
     *     tags={"Admin Blog Categories"},
     *     summary="Get blog category by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Blog category found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Blog category not found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $category = $this->adminBlogCategoryService->getCategoryById($id);

            return response()->json([
                'success' => true,
                'data' => new BlogCategoryResource($category),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Store a newly created blog category.
     *
     * @OA\Post(
     *     path="/api/admin/blog-categories",
     *     tags={"Admin Blog Categories"},
     *     summary="Create a new blog category",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description", "slug", "status", "sort_order"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="slug", type="string"),
     *             @OA\Property(property="status", type="string", enum={"draft", "published"}),
     *             @OA\Property(property="sort_order", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Blog category created successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'slug' => 'required|string|unique:blog_categories,slug|max:255',
            'status' => 'required|in:draft,published',
            'sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $category = $this->adminBlogCategoryService->createCategory($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Blog category created successfully',
                'data' => new BlogCategoryResource($category),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified blog category.
     *
     * @OA\Put(
     *     path="/api/admin/blog-categories/{id}",
     *     tags={"Admin Blog Categories"},
     *     summary="Update blog category",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="slug", type="string"),
     *             @OA\Property(property="status", type="string", enum={"draft", "published"}),
     *             @OA\Property(property="sort_order", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Blog category updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Blog category not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'slug' => 'sometimes|required|string|unique:blog_categories,slug,' . $id . '|max:255',
            'status' => 'sometimes|required|in:draft,published',
            'sort_order' => 'sometimes|required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $category = $this->adminBlogCategoryService->updateCategory($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Blog category updated successfully',
                'data' => new BlogCategoryResource($category),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Remove the specified blog category.
     *
     * @OA\Delete(
     *     path="/api/admin/blog-categories/{id}",
     *     tags={"Admin Blog Categories"},
     *     summary="Delete blog category",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Blog category deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Blog category not found"),
     *     @OA\Response(response=400, description="Cannot delete category with posts")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->adminBlogCategoryService->deleteCategory($id);

            return response()->json([
                'success' => true,
                'message' => 'Blog category deleted successfully',
            ]);
        } catch (\Exception $e) {
            $statusCode = str_contains($e->getMessage(), 'Cannot delete') ? 400 : 404;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Generate a unique slug.
     *
     * @OA\Post(
     *     path="/api/admin/blog-categories/generate-slug",
     *     tags={"Admin Blog Categories"},
     *     summary="Generate unique slug from name",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Slug generated successfully")
     * )
     */
    public function generateSlug(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $baseSlug = Str::slug($request->name);
        $slug = $baseSlug;
        $counter = 1;

        while (\App\Models\BlogCategory::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return response()->json([
            'success' => true,
            'data' => ['slug' => $slug],
        ]);
    }

    /**
     * Reorder blog categories.
     *
     * @OA\Post(
     *     path="/api/admin/blog-categories/reorder",
     *     tags={"Admin Blog Categories"},
     *     summary="Reorder blog categories",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_ids"},
     *             @OA\Property(property="category_ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Categories reordered successfully")
     * )
     */
    public function reorder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_ids' => 'required|array',
            'category_ids.*' => 'integer|exists:blog_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $categories = $this->adminBlogCategoryService->reorderCategories($request->input('category_ids'));

            return response()->json([
                'success' => true,
                'message' => 'Categories reordered successfully',
                'data' => BlogCategoryResource::collection($categories),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
