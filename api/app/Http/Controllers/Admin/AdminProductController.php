<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\AdminProductServiceInterface;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductVariantResource;
use App\Http\Resources\ProductSpecificationResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function __construct(
        private AdminProductServiceInterface $adminProductService
    ) {}

    /**
     * Display a listing of products.
     *
     * @OA\Get(
     *     path="/api/admin/products",
     *     tags={"Admin Products"},
     *     summary="Get all products with optional pagination",
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
     *         description="Filter by status",
     *         @OA\Schema(type="string", enum={"draft", "published"})
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter by category",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="brand_id",
     *         in="query",
     *         description="Filter by brand",
     *         @OA\Schema(type="integer")
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
     *         description="Items per page",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of products",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['name', 'slug', 'status', 'category_id', 'brand_id']);
        $filters['page'] = $request->query('page', 1);
        $filters['per_page'] = $request->query('per_page');

        $result = $this->adminProductService->getAllProducts($filters);

        if ($filters['per_page']) {
            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($result->items()),
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
            'data' => ProductResource::collection($result),
        ]);
    }

    /**
     * Display the specified product.
     *
     * @OA\Get(
     *     path="/api/admin/products/{id}",
     *     tags={"Admin Products"},
     *     summary="Get product by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $product = $this->adminProductService->getProductById($id);

            return response()->json([
                'success' => true,
                'data' => new ProductResource($product),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Store a newly created product.
     *
     * @OA\Post(
     *     path="/api/admin/products",
     *     tags={"Admin Products"},
     *     summary="Create a new product",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "slug", "status"},
     *             @OA\Property(property="name", type="string", example="iPhone 15"),
     *             @OA\Property(property="slug", type="string", example="iphone-15"),
     *             @OA\Property(property="short_description", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="category_id", type="integer"),
     *             @OA\Property(property="brand_id", type="integer"),
     *             @OA\Property(property="status", type="string", enum={"draft", "published"}),
     *             @OA\Property(property="base_price", type="number", format="float"),
     *             @OA\Property(property="main_image_file_id", type="integer"),
     *             @OA\Property(property="attribute_ids", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="variants", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products,slug|max:255',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'category_id' => 'nullable|integer|exists:product_categories,id',
            'brand_id' => 'nullable|integer|exists:product_brands,id',
            'status' => 'required|in:draft,published',
            'base_price' => 'nullable|numeric|min:0',
            'main_image_file_id' => 'nullable|integer|exists:files,id',
            'attribute_ids' => 'nullable|array',
            'attribute_ids.*' => 'integer|exists:attributes,id',
            'variants' => 'nullable|array',
            'variants.*.sku' => 'required_with:variants|string|max:255',
            'variants.*.slug' => 'required_with:variants|string|max:255',
            'variants.*.name' => 'nullable|string|max:255',
            'variants.*.price' => 'required_with:variants|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
            'variants.*.status' => 'nullable|in:draft,published',
            'variants.*.is_default' => 'nullable|boolean',
            'variants.*.attribute_value_ids' => 'nullable|array',
            'variants.*.attribute_value_ids.*' => 'integer|exists:attribute_values,id',
            'variants.*.images' => 'nullable|array',
            'variants.*.images.*.file_id' => 'required_with:variants.*.images|integer|exists:files,id',
            'variants.*.images.*.sort_order' => 'nullable|integer',
            'variants.*.images.*.is_primary' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $product = $this->adminProductService->createProduct($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => new ProductResource($product),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified product.
     *
     * @OA\Put(
     *     path="/api/admin/products/{id}",
     *     tags={"Admin Products"},
     *     summary="Update a product by ID",
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
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="slug", type="string"),
     *             @OA\Property(property="short_description", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="category_id", type="integer"),
     *             @OA\Property(property="brand_id", type="integer"),
     *             @OA\Property(property="status", type="string", enum={"draft", "published"}),
     *             @OA\Property(property="base_price", type="number", format="float"),
     *             @OA\Property(property="main_image_file_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Product updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Product not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:products,slug,' . $id,
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'category_id' => 'nullable|integer|exists:product_categories,id',
            'brand_id' => 'nullable|integer|exists:product_brands,id',
            'status' => 'sometimes|required|in:draft,published',
            'base_price' => 'nullable|numeric|min:0',
            'main_image_file_id' => 'nullable|integer|exists:files,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $product = $this->adminProductService->updateProduct($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => new ProductResource($product),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified product.
     *
     * @OA\Delete(
     *     path="/api/admin/products/{id}",
     *     tags={"Admin Products"},
     *     summary="Delete a product by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Product deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->adminProductService->deleteProduct($id);

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync attributes for a product.
     *
     * @OA\Post(
     *     path="/api/admin/products/{id}/attributes",
     *     tags={"Admin Products"},
     *     summary="Sync attributes for a product",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"attribute_ids"},
     *             @OA\Property(property="attribute_ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Attributes synced successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function syncAttributes(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'attribute_ids' => 'required|array',
            'attribute_ids.*' => 'integer|exists:attributes,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $product = $this->adminProductService->syncAttributes($id, $request->input('attribute_ids'));

            return response()->json([
                'success' => true,
                'message' => 'Attributes synced successfully',
                'data' => new ProductResource($product),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get variants for a product.
     *
     * @OA\Get(
     *     path="/api/admin/products/{id}/variants",
     *     tags={"Admin Products"},
     *     summary="Get all variants for a product",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by SKU or name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="slug",
     *         in="query",
     *         description="Filter by slug",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         @OA\Schema(type="string", enum={"draft", "published"})
     *     ),
     *     @OA\Response(response=200, description="List of variants"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function getVariants(Request $request, int $id): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'slug', 'status']);
            $variants = $this->adminProductService->getVariants($id, $filters);

            return response()->json([
                'success' => true,
                'data' => ProductVariantResource::collection($variants),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Add a variant to a product.
     *
     * @OA\Post(
     *     path="/api/admin/products/{id}/variants",
     *     tags={"Admin Products"},
     *     summary="Add a variant to a product",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sku", "slug", "price"},
     *             @OA\Property(property="sku", type="string"),
     *             @OA\Property(property="slug", type="string"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="stock", type="integer"),
     *             @OA\Property(property="status", type="string", enum={"draft", "published"}),
     *             @OA\Property(property="is_default", type="boolean"),
     *             @OA\Property(property="attribute_value_ids", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="images", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=201, description="Variant added successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Product not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function addVariant(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sku' => 'required|string|max:255|unique:product_variants,sku',
            'slug' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'status' => 'nullable|in:draft,published',
            'is_default' => 'nullable|boolean',
            'attribute_value_ids' => 'nullable|array',
            'attribute_value_ids.*' => 'integer|exists:attribute_values,id',
            'images' => 'nullable|array',
            'images.*.file_id' => 'required_with:images|integer|exists:files,id',
            'images.*.sort_order' => 'nullable|integer',
            'images.*.is_primary' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $variant = $this->adminProductService->addVariant($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Variant added successfully',
                'data' => new ProductVariantResource($variant),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a variant of a product.
     *
     * @OA\Put(
     *     path="/api/admin/products/{productId}/variants/{variantId}",
     *     tags={"Admin Products"},
     *     summary="Update a variant of a product",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="productId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="variantId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="sku", type="string"),
     *             @OA\Property(property="slug", type="string"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="stock", type="integer"),
     *             @OA\Property(property="status", type="string", enum={"draft", "published"}),
     *             @OA\Property(property="is_default", type="boolean"),
     *             @OA\Property(property="attribute_value_ids", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="images", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Variant updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updateVariant(Request $request, int $productId, int $variantId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sku' => 'sometimes|required|string|max:255|unique:product_variants,sku,' . $variantId,
            'slug' => 'sometimes|required|string|max:255',
            'name' => 'nullable|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'status' => 'nullable|in:draft,published',
            'is_default' => 'nullable|boolean',
            'attribute_value_ids' => 'nullable|array',
            'attribute_value_ids.*' => 'integer|exists:attribute_values,id',
            'images' => 'nullable|array',
            'images.*.file_id' => 'required_with:images|integer|exists:files,id',
            'images.*.sort_order' => 'nullable|integer',
            'images.*.is_primary' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $variant = $this->adminProductService->updateVariant($productId, $variantId, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Variant updated successfully',
                'data' => new ProductVariantResource($variant),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a variant from a product.
     *
     * @OA\Delete(
     *     path="/api/admin/products/{productId}/variants/{variantId}",
     *     tags={"Admin Products"},
     *     summary="Delete a variant from a product",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="productId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="variantId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Variant deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function deleteVariant(int $productId, int $variantId): JsonResponse
    {
        try {
            $this->adminProductService->deleteVariant($productId, $variantId);

            return response()->json([
                'success' => true,
                'message' => 'Variant deleted successfully',
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
     *     path="/api/admin/products/generate-slug",
     *     tags={"Admin Products"},
     *     summary="Generate slug from name",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="iPhone 15")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Slug generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="slug", type="string", example="iphone-15")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
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

    /**
     * Get specifications for a product.
     *
     * @OA\Get(
     *     path="/api/admin/products/{id}/specifications",
     *     tags={"Admin Products"},
     *     summary="Get all specifications for a product",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of specifications"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function getSpecifications(int $id): JsonResponse
    {
        try {
            $specifications = $this->adminProductService->getSpecifications($id);

            return response()->json([
                'success' => true,
                'data' => ProductSpecificationResource::collection($specifications),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Add a specification to a product.
     *
     * @OA\Post(
     *     path="/api/admin/products/{id}/specifications",
     *     tags={"Admin Products"},
     *     summary="Add a specification to a product",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "value"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="value", type="string"),
     *             @OA\Property(property="sort_order", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Specification added successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Product not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function addSpecification(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $specification = $this->adminProductService->addSpecification($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Specification added successfully',
                'data' => new ProductSpecificationResource($specification),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a specification of a product.
     *
     * @OA\Put(
     *     path="/api/admin/products/{productId}/specifications/{specificationId}",
     *     tags={"Admin Products"},
     *     summary="Update a specification of a product",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="productId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="specificationId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="value", type="string"),
     *             @OA\Property(property="sort_order", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Specification updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updateSpecification(Request $request, int $productId, int $specificationId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'value' => 'sometimes|required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $specification = $this->adminProductService->updateSpecification($productId, $specificationId, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Specification updated successfully',
                'data' => new ProductSpecificationResource($specification),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a specification from a product.
     *
     * @OA\Delete(
     *     path="/api/admin/products/{productId}/specifications/{specificationId}",
     *     tags={"Admin Products"},
     *     summary="Delete a specification from a product",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="productId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="specificationId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Specification deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function deleteSpecification(int $productId, int $specificationId): JsonResponse
    {
        try {
            $this->adminProductService->deleteSpecification($productId, $specificationId);

            return response()->json([
                'success' => true,
                'message' => 'Specification deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reorder specifications for a product.
     *
     * @OA\Post(
     *     path="/api/admin/products/{id}/specifications/reorder",
     *     tags={"Admin Products"},
     *     summary="Reorder specifications for a product",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"specification_ids"},
     *             @OA\Property(property="specification_ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Specifications reordered successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function reorderSpecifications(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'specification_ids' => 'required|array',
            'specification_ids.*' => 'integer|exists:product_specifications,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $specifications = $this->adminProductService->reorderSpecifications($id, $request->input('specification_ids'));

            return response()->json([
                'success' => true,
                'message' => 'Specifications reordered successfully',
                'data' => ProductSpecificationResource::collection($specifications),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
