<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\AdminProductCategoryServiceInterface;
use App\Http\Resources\ProductCategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminProductCategoryController extends Controller
{
    public function __construct(
        private AdminProductCategoryServiceInterface $adminProductCategoryService
    ) {}

    /**
     * Display a listing of product categories with tree structure.
     *
     * @OA\Get(
     *     path="/api/admin/product-categories",
     *     tags={"Admin Product Categories"},
     *     summary="Get all product categories with tree structure",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of product categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Electronics"),
     *                 @OA\Property(property="slug", type="string", example="electronics"),
     *                 @OA\Property(property="status", type="string", example="published"),
     *                 @OA\Property(property="parent_id", type="integer", nullable=true),
     *                 @OA\Property(property="subcategories", type="array", @OA\Items(type="object"))
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Read Product Categories' permission")
     * )
     */
    public function index(): JsonResponse
    {
        $categories = $this->adminProductCategoryService->getAllCategories();

        return response()->json([
            'success' => true,
            'data' => ProductCategoryResource::collection($categories),
        ]);
    }

    /**
     * Display the specified product category.
     *
     * @OA\Get(
     *     path="/api/admin/product-categories/{id}",
     *     tags={"Admin Product Categories"},
     *     summary="Get product category by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product category found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Read Product Categories' permission"),
     *     @OA\Response(response=404, description="Product category not found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $category = $this->adminProductCategoryService->getCategoryById($id);

            return response()->json([
                'success' => true,
                'data' => new ProductCategoryResource($category),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Store a newly created product category.
     *
     * @OA\Post(
     *     path="/api/admin/product-categories",
     *     tags={"Admin Product Categories"},
     *     summary="Create a new product category",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "slug", "status"},
     *             @OA\Property(property="parent_id", type="integer", nullable=true, example=null),
     *             @OA\Property(property="name", type="string", example="Electronics"),
     *             @OA\Property(property="subtitle", type="string", nullable=true, example="For photographers"),
     *             @OA\Property(property="slug", type="string", example="electronics"),
     *             @OA\Property(property="status", type="string", enum={"draft", "published"}, example="published"),
     *             @OA\Property(property="body_description", type="string", nullable=true),
     *             @OA\Property(property="logo_file_id", type="integer", nullable=true),
     *             @OA\Property(property="menu_image_file_id", type="integer", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product category created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Create Product Category' permission"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'parent_id' => 'nullable|exists:product_categories,id',
            'name' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'slug' => 'required|string|unique:product_categories,slug|max:255',
            'status' => 'required|in:draft,published',
            'body_description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'menu_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $category = $this->adminProductCategoryService->createCategory($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => new ProductCategoryResource($category),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified product category.
     *
     * @OA\Put(
     *     path="/api/admin/product-categories/{id}",
     *     tags={"Admin Product Categories"},
     *     summary="Update a product category by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="parent_id", type="integer", nullable=true),
     *             @OA\Property(property="name", type="string", example="Electronics"),
     *             @OA\Property(property="subtitle", type="string", nullable=true, example="For photographers"),
     *             @OA\Property(property="slug", type="string", example="electronics"),
     *             @OA\Property(property="status", type="string", enum={"draft", "published"}, example="published"),
     *             @OA\Property(property="body_description", type="string", nullable=true),
     *             @OA\Property(property="logo_file_id", type="integer", nullable=true),
     *             @OA\Property(property="menu_image_file_id", type="integer", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product category updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Update Product Category' permission"),
     *     @OA\Response(response=404, description="Product category not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'parent_id' => 'nullable|exists:product_categories,id',
            'name' => 'sometimes|required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:product_categories,slug,' . $id,
            'status' => 'sometimes|required|in:draft,published',
            'body_description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'menu_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $category = $this->adminProductCategoryService->updateCategory($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => new ProductCategoryResource($category),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified product category.
     *
     * @OA\Delete(
     *     path="/api/admin/product-categories/{id}",
     *     tags={"Admin Product Categories"},
     *     summary="Delete a product category by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product category deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Delete Product Category' permission"),
     *     @OA\Response(response=404, description="Product category not found"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->adminProductCategoryService->deleteCategory($id);

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate slug from name.
     *
     * @OA\Post(
     *     path="/api/admin/product-categories/generate-slug",
     *     tags={"Admin Product Categories"},
     *     summary="Generate slug from name",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Electronics")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Slug generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="slug", type="string", example="electronics")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Create Product Category' permission"),
     *     @OA\Response(response=422, description="Validation error")
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

        $slug = Str::slug($request->input('name'));

        return response()->json([
            'success' => true,
            'data' => ['slug' => $slug],
        ]);
    }
}
