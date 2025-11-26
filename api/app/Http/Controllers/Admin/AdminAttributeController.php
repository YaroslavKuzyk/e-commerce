<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\AdminAttributeServiceInterface;
use App\Http\Resources\AttributeResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminAttributeController extends Controller
{
    public function __construct(
        private AdminAttributeServiceInterface $adminAttributeService
    ) {}

    /**
     * Display a listing of attributes.
     *
     * @OA\Get(
     *     path="/api/admin/attributes",
     *     tags={"Admin Attributes"},
     *     summary="Get all attributes with optional pagination",
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
     *         name="type",
     *         in="query",
     *         description="Filter by type",
     *         @OA\Schema(type="string", enum={"select", "multi_select", "checkbox", "switch", "color"})
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
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
     *         description="Items per page",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of attributes",
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
        $filters = $request->only(['name', 'slug', 'type', 'status']);
        $filters['page'] = $request->query('page', 1);
        $filters['per_page'] = $request->query('per_page');

        $result = $this->adminAttributeService->getAllAttributes($filters);

        if ($filters['per_page']) {
            return response()->json([
                'success' => true,
                'data' => AttributeResource::collection($result->items()),
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
            'data' => AttributeResource::collection($result),
        ]);
    }

    /**
     * Display the specified attribute.
     *
     * @OA\Get(
     *     path="/api/admin/attributes/{id}",
     *     tags={"Admin Attributes"},
     *     summary="Get attribute by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attribute found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Attribute not found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $attribute = $this->adminAttributeService->getAttributeById($id);

            return response()->json([
                'success' => true,
                'data' => new AttributeResource($attribute),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Store a newly created attribute.
     *
     * @OA\Post(
     *     path="/api/admin/attributes",
     *     tags={"Admin Attributes"},
     *     summary="Create a new attribute",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "slug", "type", "status"},
     *             @OA\Property(property="name", type="string", example="Color"),
     *             @OA\Property(property="slug", type="string", example="color"),
     *             @OA\Property(property="type", type="string", enum={"select", "multi_select", "checkbox", "switch"}),
     *             @OA\Property(property="status", type="string", enum={"draft", "published"}),
     *             @OA\Property(property="sort_order", type="integer", example=0),
     *             @OA\Property(property="values", type="array", @OA\Items(
     *                 @OA\Property(property="value", type="string"),
     *                 @OA\Property(property="slug", type="string"),
     *                 @OA\Property(property="color_code", type="string"),
     *                 @OA\Property(property="sort_order", type="integer")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Attribute created successfully"
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
            'slug' => 'required|string|unique:attributes,slug|max:255',
            'type' => 'required|in:select,multi_select,checkbox,switch,color',
            'status' => 'required|in:draft,published',
            'sort_order' => 'nullable|integer',
            'values' => 'nullable|array',
            'values.*.value' => 'required_with:values|string|max:255',
            'values.*.slug' => 'required_with:values|string|max:255',
            'values.*.color_code' => 'nullable|string|max:7',
            'values.*.sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $attribute = $this->adminAttributeService->createAttribute($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Attribute created successfully',
                'data' => new AttributeResource($attribute),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified attribute.
     *
     * @OA\Put(
     *     path="/api/admin/attributes/{id}",
     *     tags={"Admin Attributes"},
     *     summary="Update an attribute by ID",
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
     *             @OA\Property(property="type", type="string", enum={"select", "multi_select", "checkbox", "switch"}),
     *             @OA\Property(property="status", type="string", enum={"draft", "published"}),
     *             @OA\Property(property="sort_order", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Attribute updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Attribute not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:attributes,slug,' . $id,
            'type' => 'sometimes|required|in:select,multi_select,checkbox,switch,color',
            'status' => 'sometimes|required|in:draft,published',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $attribute = $this->adminAttributeService->updateAttribute($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Attribute updated successfully',
                'data' => new AttributeResource($attribute),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified attribute.
     *
     * @OA\Delete(
     *     path="/api/admin/attributes/{id}",
     *     tags={"Admin Attributes"},
     *     summary="Delete an attribute by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Attribute deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Attribute not found")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->adminAttributeService->deleteAttribute($id);

            return response()->json([
                'success' => true,
                'message' => 'Attribute deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add a value to an attribute.
     *
     * @OA\Post(
     *     path="/api/admin/attributes/{id}/values",
     *     tags={"Admin Attributes"},
     *     summary="Add a value to an attribute",
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
     *             required={"value", "slug"},
     *             @OA\Property(property="value", type="string", example="Red"),
     *             @OA\Property(property="slug", type="string", example="red"),
     *             @OA\Property(property="color_code", type="string", example="#FF0000"),
     *             @OA\Property(property="sort_order", type="integer", example=0)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Value added successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Attribute not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function addValue(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'color_code' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $attribute = $this->adminAttributeService->addValue($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Value added successfully',
                'data' => new AttributeResource($attribute),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a value of an attribute.
     *
     * @OA\Put(
     *     path="/api/admin/attributes/{id}/values/{valueId}",
     *     tags={"Admin Attributes"},
     *     summary="Update a value of an attribute",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="valueId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="value", type="string"),
     *             @OA\Property(property="slug", type="string"),
     *             @OA\Property(property="color_code", type="string"),
     *             @OA\Property(property="sort_order", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Value updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function updateValue(Request $request, int $id, int $valueId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'value' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255',
            'color_code' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $attribute = $this->adminAttributeService->updateValue($id, $valueId, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Value updated successfully',
                'data' => new AttributeResource($attribute),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a value from an attribute.
     *
     * @OA\Delete(
     *     path="/api/admin/attributes/{id}/values/{valueId}",
     *     tags={"Admin Attributes"},
     *     summary="Delete a value from an attribute",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="valueId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Value deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function deleteValue(int $id, int $valueId): JsonResponse
    {
        try {
            $this->adminAttributeService->deleteValue($id, $valueId);

            return response()->json([
                'success' => true,
                'message' => 'Value deleted successfully',
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
     *     path="/api/admin/attributes/generate-slug",
     *     tags={"Admin Attributes"},
     *     summary="Generate slug from name",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Color")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Slug generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="slug", type="string", example="color")
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
}
