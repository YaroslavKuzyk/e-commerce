<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\ProductCategoryResource;
use App\Http\Resources\ProductCategoryFlatResource;
use App\Contracts\CustomerProductCategoryServiceInterface;

class CustomerProductCategoryController extends Controller
{
    public function __construct(
        private CustomerProductCategoryServiceInterface $customerProductCategoryService
    ) {}


    /**
     * Display a listing of product categories with tree structure.
     *
     * @OA\Get(
     *     path="/api/product-categories",
     *     tags={"Customer Product Categories"},
     *     summary="Get all product categories with tree structure",
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
     * )
     */
    public function index(): JsonResponse
    {
        $categories = $this->customerProductCategoryService->getAllCategories();

        return response()->json([
            'success' => true,
            'data' => ProductCategoryResource::collection($categories),
        ]);
    }

    /**
     * Display a paginated flat listing of product categories.
     *
     * @OA\Get(
     *     path="/api/product-categories/flat",
     *     tags={"Customer Product Categories"},
     *     summary="Get paginated flat list of product categories",
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
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of product categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Electronics"),
     *                 @OA\Property(property="slug", type="string", example="electronics"),
     *                 @OA\Property(property="status", type="string", example="published"),
     *                 @OA\Property(property="parent_id", type="integer", nullable=true)
     *             )),
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
    public function flatIndex(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 15);

        $categories = $this->customerProductCategoryService->getCategoriesPaginated($page, $limit);

        return response()->json([
            'success' => true,
            'data' => ProductCategoryFlatResource::collection($categories),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ],
        ]);
    }

    /**
     * Display the specified product category.
     *
     * @OA\Get(
     *     path="/api/product-categories/{id}",
     *     tags={"Customer Product Categories"},
     *     summary="Get product category by ID",
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
     *     @OA\Response(response=404, description="Product category not found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $category = $this->customerProductCategoryService->getCategoryById($id);

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
     * Resolve a category path (array of slugs) to category data with breadcrumbs.
     */
    public function resolvePath(Request $request): JsonResponse
    {
        $pathSlugs = $request->query('path', []);

        if (empty($pathSlugs)) {
            return response()->json([
                'success' => false,
                'message' => 'Path is required',
            ], 400);
        }

        $categories = [];
        $parentId = null;

        foreach ($pathSlugs as $slug) {
            $query = \App\Models\ProductCategory::where('slug', $slug)
                ->published();

            if ($parentId === null) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $parentId);
            }

            $category = $query->first();

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => "Category not found: {$slug}",
                ], 404);
            }

            $categories[] = $category;
            $parentId = $category->id;
        }

        $deepestCategory = end($categories);
        $deepestCategory->load(['subcategories' => function ($q) {
            $q->published()->withCount(['products' => function ($query) {
                $query->where('status', 'published');
            }]);
        }]);

        return response()->json([
            'success' => true,
            'data' => [
                'category' => new ProductCategoryResource($deepestCategory),
                'breadcrumbs' => ProductCategoryResource::collection($categories),
                'category_id' => $deepestCategory->id,
            ],
        ]);
    }

    /**
     * Get category by slug with breadcrumbs.
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $category = \App\Models\ProductCategory::where('slug', $slug)
            ->published()
            ->with(['subcategories' => function ($q) {
                $q->published()->withCount(['products' => function ($query) {
                    $query->where('status', 'published');
                }]);
            }, 'parent'])
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        // Build breadcrumbs by traversing up the parent chain
        $breadcrumbs = [];
        $current = $category;
        while ($current) {
            array_unshift($breadcrumbs, $current);
            $current = $current->parent;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'category' => new ProductCategoryResource($category),
                'breadcrumbs' => ProductCategoryResource::collection($breadcrumbs),
                'category_id' => $category->id,
            ],
        ]);
    }
}