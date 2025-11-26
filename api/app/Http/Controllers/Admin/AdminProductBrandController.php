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
     */
    public function index(): JsonResponse
    {
        $brands = $this->adminProductBrandService->getAllBrands();

        return response()->json([
            'success' => true,
            'data' => ProductBrandResource::collection($brands),
        ]);
    }

    /**
     * Display the specified product brand.
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
