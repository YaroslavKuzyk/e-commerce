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
