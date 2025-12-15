<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Contracts\CustomerProductServiceInterface;

class CustomerProductController extends Controller
{
    public function __construct(
        private CustomerProductServiceInterface $customerProductService
    ) {}

    /**
     * Display a paginated listing of products.
     *
     * @OA\Get(
     *     path="/api/products",
     *     tags={"Customer Products"},
     *     summary="Get paginated list of products",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="brand_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of products",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 15);

        $filters = [];
        if ($request->has('category_id')) {
            $filters['category_id'] = $request->query('category_id');
        }
        if ($request->has('brand_id')) {
            $filters['brand_id'] = $request->query('brand_id');
        }

        $products = $this->customerProductService->getProductsPaginated($page, $limit, $filters);

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Display the specified product by ID.
     *
     * @OA\Get(
     *     path="/api/products/{id}",
     *     tags={"Customer Products"},
     *     summary="Get product by ID",
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
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $product = $this->customerProductService->getProductById($id);

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
     * Display the specified product by slug.
     *
     * @OA\Get(
     *     path="/api/products/slug/{slug}",
     *     tags={"Customer Products"},
     *     summary="Get product by slug",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function showBySlug(string $slug): JsonResponse
    {
        try {
            $product = $this->customerProductService->getProductBySlug($slug);

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
}
