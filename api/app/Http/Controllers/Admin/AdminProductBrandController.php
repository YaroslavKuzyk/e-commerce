<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\AdminProductBrandServiceInterface;
use App\Http\Resources\ProductBrandResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminProductBrandController extends Controller
{
    public function __construct(
        private AdminProductBrandServiceInterface $adminProductBrandService
    ) {}

    /**
     * Display a listing of product brands.
     *
     * @OA\Get(
     *     path="/api/admin/product-brands",
     *     tags={"Admin Product Brands"},
     *     summary="Get all product brands with optional pagination",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Search by name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="slug",
     *         in="query",
     *         description="Search by slug",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status (draft/published)",
     *         @OA\Schema(type="string", enum={"draft", "published"})
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page (if not provided, returns all items)",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of product brands",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Apple"),
     *                 @OA\Property(property="slug", type="string", example="apple"),
     *                 @OA\Property(property="status", type="string", example="published")
     *             )),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=73)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Read Product Brands' permission")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['name', 'slug', 'status']);
        $filters['page'] = $request->query('page', 1);
        $filters['per_page'] = $request->query('per_page');

        $result = $this->adminProductBrandService->getAllBrands($filters);

        if ($filters['per_page']) {
            return response()->json([
                'success' => true,
                'data' => ProductBrandResource::collection($result->items()),
                'meta' => [
                    'current_page' => $result->currentPage(),
                    'last_page' => $result->lastPage(),
                    'per_page' => $result->perPage(),
                    'total' => $result->total(),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => ProductBrandResource::collection($result),
        ]);
    }

    /**
     * Display the specified product brand.
     *
     * @OA\Get(
     *     path="/api/admin/product-brands/{id}",
     *     tags={"Admin Product Brands"},
     *     summary="Get product brand by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product brand found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Read Product Brands' permission"),
     *     @OA\Response(response=404, description="Product brand not found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $brand = $this->adminProductBrandService->getBrandById($id);

            return response()->json([
                'success' => true,
                'data' => new ProductBrandResource($brand),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Store a newly created product brand.
     *
     * @OA\Post(
     *     path="/api/admin/product-brands",
     *     tags={"Admin Product Brands"},
     *     summary="Create a new product brand",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "slug", "status"},
     *             @OA\Property(property="name", type="string", example="Apple"),
     *             @OA\Property(property="slug", type="string", example="apple"),
     *             @OA\Property(property="status", type="string", enum={"draft", "published"}, example="published"),
     *             @OA\Property(property="body_description", type="string", nullable=true),
     *             @OA\Property(property="logo_file_id", type="integer", nullable=true),
     *             @OA\Property(property="menu_image_file_id", type="integer", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product brand created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Brand created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Create Product Brand' permission"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:product_brands,slug|max:255',
            'status' => 'required|in:draft,published',
            'body_description' => 'nullable|string',
            'logo_file_id' => 'nullable|integer|exists:files,id',
            'menu_image_file_id' => 'nullable|integer|exists:files,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $brand = $this->adminProductBrandService->createBrand($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Brand created successfully',
                'data' => new ProductBrandResource($brand),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified product brand.
     *
     * @OA\Put(
     *     path="/api/admin/product-brands/{id}",
     *     tags={"Admin Product Brands"},
     *     summary="Update a product brand by ID",
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
     *             @OA\Property(property="name", type="string", example="Apple"),
     *             @OA\Property(property="slug", type="string", example="apple"),
     *             @OA\Property(property="status", type="string", enum={"draft", "published"}, example="published"),
     *             @OA\Property(property="body_description", type="string", nullable=true),
     *             @OA\Property(property="logo_file_id", type="integer", nullable=true),
     *             @OA\Property(property="menu_image_file_id", type="integer", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product brand updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Brand updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Update Product Brand' permission"),
     *     @OA\Response(response=404, description="Product brand not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:product_brands,slug,' . $id,
            'status' => 'sometimes|required|in:draft,published',
            'body_description' => 'nullable|string',
            'logo_file_id' => 'nullable|integer|exists:files,id',
            'menu_image_file_id' => 'nullable|integer|exists:files,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $brand = $this->adminProductBrandService->updateBrand($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Brand updated successfully',
                'data' => new ProductBrandResource($brand),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified product brand.
     *
     * @OA\Delete(
     *     path="/api/admin/product-brands/{id}",
     *     tags={"Admin Product Brands"},
     *     summary="Delete a product brand by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product brand deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Brand deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Delete Product Brand' permission"),
     *     @OA\Response(response=404, description="Product brand not found"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->adminProductBrandService->deleteBrand($id);

            return response()->json([
                'success' => true,
                'message' => 'Brand deleted successfully',
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
     *     path="/api/admin/product-brands/generate-slug",
     *     tags={"Admin Product Brands"},
     *     summary="Generate slug from name",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Apple")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Slug generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="slug", type="string", example="apple")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Create Product Brand' permission"),
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
