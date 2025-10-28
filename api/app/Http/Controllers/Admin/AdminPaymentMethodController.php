<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\AdminPaymentMethodServiceInterface;
use App\Http\Resources\PaymentMethodResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminPaymentMethodController extends Controller
{
    public function __construct(
        private AdminPaymentMethodServiceInterface $adminPaymentMethodService
    ) {}

    /**
     * Display a listing of payment methods.
     */
    public function index(): JsonResponse
    {
        $paymentMethods = $this->adminPaymentMethodService->getAllPaymentMethods();

        return response()->json([
            'success' => true,
            'data' => PaymentMethodResource::collection($paymentMethods),
        ]);
    }

    /**
     * Display the specified payment method.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $paymentMethod = $this->adminPaymentMethodService->getPaymentMethodById($id);

            return response()->json([
                'success' => true,
                'data' => new PaymentMethodResource($paymentMethod),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Store a newly created payment method.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_uk' => 'nullable|string|max:255',
            'code' => 'required|string|unique:payment_methods,code|max:255',
            'description' => 'nullable|string',
            'description_uk' => 'nullable|string',
            'type' => 'required|in:cash_on_delivery,online',
            'provider' => 'nullable|string|max:255',
            'provider_config' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $paymentMethod = $this->adminPaymentMethodService->createPaymentMethod($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Payment method created successfully',
                'data' => new PaymentMethodResource($paymentMethod),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified payment method.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'name_uk' => 'nullable|string|max:255',
            'code' => 'sometimes|required|string|max:255|unique:payment_methods,code,' . $id,
            'description' => 'nullable|string',
            'description_uk' => 'nullable|string',
            'type' => 'sometimes|required|in:cash_on_delivery,online',
            'provider' => 'nullable|string|max:255',
            'provider_config' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $paymentMethod = $this->adminPaymentMethodService->updatePaymentMethod($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Payment method updated successfully',
                'data' => new PaymentMethodResource($paymentMethod),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Toggle active status of a payment method.
     */
    public function toggleActive(int $id): JsonResponse
    {
        try {
            $paymentMethod = $this->adminPaymentMethodService->togglePaymentMethodActive($id);

            return response()->json([
                'success' => true,
                'message' => 'Payment method status toggled successfully',
                'data' => new PaymentMethodResource($paymentMethod),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
