<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\AdminDeliveryMethodServiceInterface;
use App\Http\Resources\DeliveryMethodResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminDeliveryMethodController extends Controller
{
    public function __construct(
        private AdminDeliveryMethodServiceInterface $adminDeliveryMethodService
    ) {}

    /**
     * Display a listing of delivery methods.
     *
     * @OA\Get(
     *     path="/api/admin/delivery-methods",
     *     tags={"Admin Delivery Methods"},
     *     summary="Get all delivery methods",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of delivery methods",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Nova Poshta"),
     *                 @OA\Property(property="code", type="string", example="nova_poshta"),
     *                 @OA\Property(property="is_active", type="boolean", example=true)
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Read Delivery Methods' permission")
     * )
     */
    public function index(): JsonResponse
    {
        $deliveryMethods = $this->adminDeliveryMethodService->getAllDeliveryMethods();

        return response()->json([
            'success' => true,
            'data' => DeliveryMethodResource::collection($deliveryMethods),
        ]);
    }

    /**
     * Display the specified delivery method.
     *
     * @OA\Get(
     *     path="/api/admin/delivery-methods/{id}",
     *     tags={"Admin Delivery Methods"},
     *     summary="Get delivery method by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Delivery method found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Read Delivery Methods' permission"),
     *     @OA\Response(response=404, description="Delivery method not found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $deliveryMethod = $this->adminDeliveryMethodService->getDeliveryMethodById($id);

            return response()->json([
                'success' => true,
                'data' => new DeliveryMethodResource($deliveryMethod),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Store a newly created delivery method.
     *
     * @OA\Post(
     *     path="/api/admin/delivery-methods",
     *     tags={"Admin Delivery Methods"},
     *     summary="Create a new delivery method",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "code"},
     *             @OA\Property(property="name", type="string", example="Nova Poshta"),
     *             @OA\Property(property="name_uk", type="string", example="Нова Пошта"),
     *             @OA\Property(property="code", type="string", example="nova_poshta"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="description_uk", type="string"),
     *             @OA\Property(property="has_api", type="boolean", example=true),
     *             @OA\Property(property="api_config", type="object"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="sort_order", type="integer", example=0),
     *             @OA\Property(property="payment_method_ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Delivery method created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Delivery method created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Create Delivery Method' permission"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_uk' => 'nullable|string|max:255',
            'code' => 'required|string|unique:delivery_methods,code|max:255',
            'description' => 'nullable|string',
            'description_uk' => 'nullable|string',
            'has_api' => 'boolean',
            'api_config' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'payment_method_ids' => 'nullable|array',
            'payment_method_ids.*' => 'exists:payment_methods,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $deliveryMethod = $this->adminDeliveryMethodService->createDeliveryMethod($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Delivery method created successfully',
                'data' => new DeliveryMethodResource($deliveryMethod),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified delivery method.
     *
     * @OA\Put(
     *     path="/api/admin/delivery-methods/{id}",
     *     tags={"Admin Delivery Methods"},
     *     summary="Update a delivery method by ID",
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
     *             @OA\Property(property="name", type="string", example="Nova Poshta"),
     *             @OA\Property(property="name_uk", type="string", example="Нова Пошта"),
     *             @OA\Property(property="code", type="string", example="nova_poshta"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="description_uk", type="string"),
     *             @OA\Property(property="has_api", type="boolean", example=true),
     *             @OA\Property(property="api_config", type="object"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="sort_order", type="integer", example=0),
     *             @OA\Property(property="payment_method_ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Delivery method updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Delivery method updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Update Delivery Method' permission"),
     *     @OA\Response(response=404, description="Delivery method not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'name_uk' => 'nullable|string|max:255',
            'code' => 'sometimes|required|string|max:255|unique:delivery_methods,code,' . $id,
            'description' => 'nullable|string',
            'description_uk' => 'nullable|string',
            'has_api' => 'boolean',
            'api_config' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'payment_method_ids' => 'nullable|array',
            'payment_method_ids.*' => 'exists:payment_methods,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $deliveryMethod = $this->adminDeliveryMethodService->updateDeliveryMethod($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Delivery method updated successfully',
                'data' => new DeliveryMethodResource($deliveryMethod),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Toggle active status of a delivery method.
     *
     * @OA\Patch(
     *     path="/api/admin/delivery-methods/{id}/toggle-active",
     *     tags={"Admin Delivery Methods"},
     *     summary="Toggle active status of a delivery method",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Delivery method status toggled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Delivery method status toggled successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Update Delivery Method' permission"),
     *     @OA\Response(response=404, description="Delivery method not found")
     * )
     */
    public function toggleActive(int $id): JsonResponse
    {
        try {
            $deliveryMethod = $this->adminDeliveryMethodService->toggleDeliveryMethodActive($id);

            return response()->json([
                'success' => true,
                'message' => 'Delivery method status toggled successfully',
                'data' => new DeliveryMethodResource($deliveryMethod),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Sync payment methods for a delivery method.
     *
     * @OA\Post(
     *     path="/api/admin/delivery-methods/{id}/payment-methods",
     *     tags={"Admin Delivery Methods"},
     *     summary="Sync payment methods for a delivery method",
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
     *             required={"payment_method_ids"},
     *             @OA\Property(property="payment_method_ids", type="array", @OA\Items(type="integer"), example={1, 2, 3})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment methods synced successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment methods synced successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Update Delivery Method' permission"),
     *     @OA\Response(response=404, description="Delivery method not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function syncPaymentMethods(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_method_ids' => 'required|array',
            'payment_method_ids.*' => 'exists:payment_methods,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $deliveryMethod = $this->adminDeliveryMethodService->syncPaymentMethods($id, $request->payment_method_ids);

            return response()->json([
                'success' => true,
                'message' => 'Payment methods synced successfully',
                'data' => new DeliveryMethodResource($deliveryMethod),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Toggle payment method active status for a delivery method.
     *
     * @OA\Patch(
     *     path="/api/admin/delivery-methods/{deliveryMethodId}/payment-methods/{paymentMethodId}/toggle-active",
     *     tags={"Admin Delivery Methods"},
     *     summary="Toggle payment method active status for a delivery method",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="deliveryMethodId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="paymentMethodId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment method status toggled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment method status toggled successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Update Delivery Method' permission"),
     *     @OA\Response(response=404, description="Delivery or payment method not found")
     * )
     */
    public function togglePaymentMethodActive(int $deliveryMethodId, int $paymentMethodId): JsonResponse
    {
        try {
            $this->adminDeliveryMethodService->togglePaymentMethodActive($deliveryMethodId, $paymentMethodId);

            return response()->json([
                'success' => true,
                'message' => 'Payment method status toggled successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
