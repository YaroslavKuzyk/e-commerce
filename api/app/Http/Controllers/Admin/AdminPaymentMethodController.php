<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\Services\Admin\PaymentMethodServiceInterface;
use App\Http\Resources\PaymentMethodResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminPaymentMethodController extends Controller
{
    public function __construct(
        private PaymentMethodServiceInterface $adminPaymentMethodService
    ) {}

    /**
     * Display a listing of payment methods.
     *
     * @OA\Get(
     *     path="/api/admin/payment-methods",
     *     tags={"Admin Payment Methods"},
     *     summary="Get all payment methods",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of payment methods",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Cash on Delivery"),
     *                 @OA\Property(property="code", type="string", example="cod"),
     *                 @OA\Property(property="type", type="string", example="cash_on_delivery"),
     *                 @OA\Property(property="is_active", type="boolean", example=true)
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Read Payment Methods' permission")
     * )
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
     *
     * @OA\Get(
     *     path="/api/admin/payment-methods/{id}",
     *     tags={"Admin Payment Methods"},
     *     summary="Get payment method by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment method found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Read Payment Methods' permission"),
     *     @OA\Response(response=404, description="Payment method not found")
     * )
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
     *
     * @OA\Post(
     *     path="/api/admin/payment-methods",
     *     tags={"Admin Payment Methods"},
     *     summary="Create a new payment method",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "code", "type"},
     *             @OA\Property(property="name", type="string", example="Cash on Delivery"),
     *             @OA\Property(property="name_uk", type="string", example="Оплата при отриманні"),
     *             @OA\Property(property="code", type="string", example="cod"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="description_uk", type="string"),
     *             @OA\Property(property="type", type="string", enum={"cash_on_delivery", "online"}, example="cash_on_delivery"),
     *             @OA\Property(property="provider", type="string", example="liqpay"),
     *             @OA\Property(property="provider_config", type="object"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="sort_order", type="integer", example=0)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Payment method created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment method created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Create Payment Method' permission"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
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
     *
     * @OA\Put(
     *     path="/api/admin/payment-methods/{id}",
     *     tags={"Admin Payment Methods"},
     *     summary="Update a payment method by ID",
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
     *             @OA\Property(property="name", type="string", example="Cash on Delivery"),
     *             @OA\Property(property="name_uk", type="string", example="Оплата при отриманні"),
     *             @OA\Property(property="code", type="string", example="cod"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="description_uk", type="string"),
     *             @OA\Property(property="type", type="string", enum={"cash_on_delivery", "online"}, example="cash_on_delivery"),
     *             @OA\Property(property="provider", type="string", example="liqpay"),
     *             @OA\Property(property="provider_config", type="object"),
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *             @OA\Property(property="sort_order", type="integer", example=0)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment method updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment method updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Update Payment Method' permission"),
     *     @OA\Response(response=404, description="Payment method not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
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
     *
     * @OA\Patch(
     *     path="/api/admin/payment-methods/{id}/toggle-active",
     *     tags={"Admin Payment Methods"},
     *     summary="Toggle active status of a payment method",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment method status toggled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment method status toggled successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Update Payment Method' permission"),
     *     @OA\Response(response=404, description="Payment method not found")
     * )
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
