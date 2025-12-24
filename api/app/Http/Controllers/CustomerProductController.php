<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductVariantCatalogResource;
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
     *     summary="Get paginated list of products with filters",
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string"), description="Search in name, slug, description"),
     *     @OA\Parameter(name="category_id", in="query", required=false, @OA\Schema(type="integer"), description="Category ID (includes subcategories)"),
     *     @OA\Parameter(name="brand_ids", in="query", required=false, @OA\Schema(type="string"), description="Comma-separated brand IDs"),
     *     @OA\Parameter(name="price_min", in="query", required=false, @OA\Schema(type="number")),
     *     @OA\Parameter(name="price_max", in="query", required=false, @OA\Schema(type="number")),
     *     @OA\Parameter(name="attribute_values", in="query", required=false, @OA\Schema(type="string"), description="Comma-separated attribute value IDs"),
     *     @OA\Parameter(name="in_stock", in="query", required=false, @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="sort_by", in="query", required=false, @OA\Schema(type="string", enum={"created_at", "name", "price_asc", "price_desc"})),
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

        // Search
        if ($request->has('search')) {
            $filters['search'] = $request->query('search');
        }

        // Category (with subcategories support)
        if ($request->has('category_id')) {
            $filters['category_id'] = $request->query('category_id');
        }

        // Single brand (backwards compatible)
        if ($request->has('brand_id')) {
            $filters['brand_id'] = $request->query('brand_id');
        }

        // Multiple brands
        if ($request->has('brand_ids')) {
            $filters['brand_ids'] = $request->query('brand_ids');
        }

        // Price range
        if ($request->has('price_min')) {
            $filters['price_min'] = $request->query('price_min');
        }
        if ($request->has('price_max')) {
            $filters['price_max'] = $request->query('price_max');
        }

        // Attribute values
        if ($request->has('attribute_values')) {
            $filters['attribute_values'] = $request->query('attribute_values');
        }

        // Specifications (passed as specs[name]=value)
        if ($request->has('specs')) {
            $filters['specifications'] = $request->query('specs');
        }

        // In stock filter
        if ($request->has('in_stock')) {
            $filters['in_stock'] = filter_var($request->query('in_stock'), FILTER_VALIDATE_BOOLEAN);
        }

        // Has discount filter (Акції)
        if ($request->has('has_discount')) {
            $filters['has_discount'] = filter_var($request->query('has_discount'), FILTER_VALIDATE_BOOLEAN);
        }

        // Is clearance filter (Уцінка)
        if ($request->has('is_clearance')) {
            $filters['is_clearance'] = filter_var($request->query('is_clearance'), FILTER_VALIDATE_BOOLEAN);
        }

        // Sorting
        if ($request->has('sort_by')) {
            $filters['sort_by'] = $request->query('sort_by');
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
     * Get available filters for products.
     *
     * @OA\Get(
     *     path="/api/products/filters",
     *     tags={"Customer Products"},
     *     summary="Get available filters for products",
     *     @OA\Parameter(name="category_id", in="query", required=false, @OA\Schema(type="integer"), description="Narrow filters to category"),
     *     @OA\Response(
     *         response=200,
     *         description="Available filters",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="price_range", type="object",
     *                     @OA\Property(property="min", type="number"),
     *                     @OA\Property(property="max", type="number")
     *                 ),
     *                 @OA\Property(property="categories", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="brands", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="attributes", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="specifications", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     )
     * )
     */
    public function filters(Request $request): JsonResponse
    {
        $currentFilters = [];

        if ($request->has('category_id')) {
            $currentFilters['category_id'] = $request->query('category_id');
        }

        $filters = $this->customerProductService->getAvailableFilters($currentFilters);

        return response()->json([
            'success' => true,
            'data' => $filters,
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

    /**
     * Get products filtered by slugs instead of IDs.
     */
    public function indexBySlugs(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 15);
        $filters = [];

        // Category slug resolution
        if ($request->has('category_slug')) {
            $category = \App\Models\ProductCategory::where('slug', $request->query('category_slug'))->first();
            if ($category) {
                $filters['category_id'] = $category->id;
            }
        }

        // Brand slugs resolution
        if ($request->has('brand_slugs')) {
            $brandSlugs = explode(',', $request->query('brand_slugs'));
            $brandIds = \App\Models\ProductBrand::whereIn('slug', $brandSlugs)->pluck('id')->toArray();
            if (!empty($brandIds)) {
                $filters['brand_ids'] = implode(',', $brandIds);
            }
        }

        // Attribute value slugs resolution
        // Format: attr_slugs[color]=black,white&attr_slugs[size]=large
        if ($request->has('attr_slugs')) {
            $attributeValueIds = [];
            foreach ($request->query('attr_slugs') as $attrSlug => $valueSlugs) {
                $attribute = \App\Models\Attribute::where('slug', $attrSlug)->first();
                if ($attribute) {
                    $valueIds = \App\Models\AttributeValue::where('attribute_id', $attribute->id)
                        ->whereIn('slug', explode(',', $valueSlugs))
                        ->pluck('id')
                        ->toArray();
                    $attributeValueIds = array_merge($attributeValueIds, $valueIds);
                }
            }
            if (!empty($attributeValueIds)) {
                $filters['attribute_values'] = implode(',', $attributeValueIds);
            }
        }

        // Search
        if ($request->has('search')) {
            $filters['search'] = $request->query('search');
        }

        // Price range
        if ($request->has('price_min')) {
            $filters['price_min'] = $request->query('price_min');
        }
        if ($request->has('price_max')) {
            $filters['price_max'] = $request->query('price_max');
        }

        // In stock
        if ($request->has('in_stock')) {
            $filters['in_stock'] = filter_var($request->query('in_stock'), FILTER_VALIDATE_BOOLEAN);
        }

        // Has discount filter (Акції)
        if ($request->has('has_discount')) {
            $filters['has_discount'] = filter_var($request->query('has_discount'), FILTER_VALIDATE_BOOLEAN);
        }

        // Is clearance filter (Уцінка)
        if ($request->has('is_clearance')) {
            $filters['is_clearance'] = filter_var($request->query('is_clearance'), FILTER_VALIDATE_BOOLEAN);
        }

        // Sort
        if ($request->has('sort_by')) {
            $filters['sort_by'] = $request->query('sort_by');
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
     * Get all attribute slugs for frontend URL parsing.
     */
    public function attributeSlugs(): JsonResponse
    {
        $attributes = \App\Models\Attribute::select('slug')->get()->pluck('slug');

        return response()->json([
            'success' => true,
            'data' => $attributes,
        ]);
    }

    /**
     * Get filters by category slug.
     */
    public function filtersByCategorySlug(Request $request): JsonResponse
    {
        $currentFilters = [];

        if ($request->has('category_slug')) {
            $category = \App\Models\ProductCategory::where('slug', $request->query('category_slug'))->first();
            if ($category) {
                $currentFilters['category_id'] = $category->id;
            }
        }

        $filters = $this->customerProductService->getAvailableFilters($currentFilters);

        return response()->json([
            'success' => true,
            'data' => $filters,
        ]);
    }

    /**
     * Get product variants as separate catalog items.
     * Each variant is returned as a separate item with product info included.
     */
    public function variants(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 15);
        $filters = [];

        // Fetch specific variants by IDs (for cart/favorites)
        if ($request->has('ids')) {
            $ids = array_map('intval', explode(',', $request->query('ids')));
            $variants = \App\Models\ProductVariant::whereIn('id', $ids)
                ->where('status', 'published')
                ->with([
                    'product.category.parent',
                    'product.brand',
                    'product.mainImage',
                    'attributeValues.attribute',
                    'images.file',
                ])
                ->get();

            return response()->json([
                'success' => true,
                'data' => ProductVariantCatalogResource::collection($variants),
            ]);
        }

        // Category slug resolution
        if ($request->has('category_slug')) {
            $category = \App\Models\ProductCategory::where('slug', $request->query('category_slug'))->first();
            if ($category) {
                $filters['category_id'] = $category->id;
            }
        }

        // Brand slugs resolution
        if ($request->has('brand_slugs')) {
            $brandSlugs = explode(',', $request->query('brand_slugs'));
            $brandIds = \App\Models\ProductBrand::whereIn('slug', $brandSlugs)->pluck('id')->toArray();
            if (!empty($brandIds)) {
                $filters['brand_ids'] = implode(',', $brandIds);
            }
        }

        // Attribute value slugs resolution
        if ($request->has('attr_slugs')) {
            $attributeValueIds = [];
            foreach ($request->query('attr_slugs') as $attrSlug => $valueSlugs) {
                $attribute = \App\Models\Attribute::where('slug', $attrSlug)->first();
                if ($attribute) {
                    $valueIds = \App\Models\AttributeValue::where('attribute_id', $attribute->id)
                        ->whereIn('slug', explode(',', $valueSlugs))
                        ->pluck('id')
                        ->toArray();
                    $attributeValueIds = array_merge($attributeValueIds, $valueIds);
                }
            }
            if (!empty($attributeValueIds)) {
                $filters['attribute_values'] = implode(',', $attributeValueIds);
            }
        }

        // Search
        if ($request->has('search')) {
            $filters['search'] = $request->query('search');
        }

        // Price range
        if ($request->has('price_min')) {
            $filters['price_min'] = $request->query('price_min');
        }
        if ($request->has('price_max')) {
            $filters['price_max'] = $request->query('price_max');
        }

        // In stock
        if ($request->has('in_stock')) {
            $filters['in_stock'] = filter_var($request->query('in_stock'), FILTER_VALIDATE_BOOLEAN);
        }

        // Has discount filter (Акції)
        if ($request->has('has_discount')) {
            $filters['has_discount'] = filter_var($request->query('has_discount'), FILTER_VALIDATE_BOOLEAN);
        }

        // Is clearance filter (Уцінка)
        if ($request->has('is_clearance')) {
            $filters['is_clearance'] = filter_var($request->query('is_clearance'), FILTER_VALIDATE_BOOLEAN);
        }

        // Sort
        if ($request->has('sort_by')) {
            $filters['sort_by'] = $request->query('sort_by');
        }

        $variants = $this->customerProductService->getVariantsPaginated($page, $limit, $filters);

        return response()->json([
            'success' => true,
            'data' => ProductVariantCatalogResource::collection($variants),
            'meta' => [
                'current_page' => $variants->currentPage(),
                'last_page' => $variants->lastPage(),
                'per_page' => $variants->perPage(),
                'total' => $variants->total(),
            ],
        ]);
    }
}
