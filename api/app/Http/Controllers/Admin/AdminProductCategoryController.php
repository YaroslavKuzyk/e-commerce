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
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'parent_id' => 'nullable|exists:product_categories,id',
            'name' => 'required|string|max:255',
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
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'parent_id' => 'nullable|exists:product_categories,id',
            'name' => 'sometimes|required|string|max:255',
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
