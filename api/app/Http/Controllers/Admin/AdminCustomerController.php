<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\Services\Admin\AdminCustomerServiceInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminCustomerController extends Controller
{
    public function __construct(
        private AdminCustomerServiceInterface $adminCustomerService
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/admin/customers",
     *     tags={"Admin Customers"},
     *     summary="Get all customers",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name or email",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status (active/inactive)",
     *         @OA\Schema(type="string", enum={"active", "inactive"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of customers",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="status", type="string", example="active")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Read Customers' permission"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status']);
        $customers = $this->adminCustomerService->getAllCustomers($filters);

        return response()->json([
            'success' => true,
            'data' => $customers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/api/admin/customers",
     *     tags={"Admin Customers"},
     *     summary="Create a new customer",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active"),
     *             @OA\Property(property="password", type="string", example="SecurePass123", description="Required password (minimum 8 characters)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Customer created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Customer created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Create Customer' permission"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'status' => 'nullable|in:active,inactive',
            'password' => 'required|string|min:8',
        ]);

        $user = $this->adminCustomerService->createCustomer($validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
            'data' => $user,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/admin/customers/{customer}",
     *     tags={"Admin Customers"},
     *     summary="Get a customer by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="customer",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Read Customers' permission"),
     *     @OA\Response(response=404, description="Customer not found"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function show(User $customer): JsonResponse
    {
        $customer = $this->adminCustomerService->getCustomerById($customer);

        return response()->json([
            'success' => true,
            'data' => $customer,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/api/admin/customers/{customer}",
     *     tags={"Admin Customers"},
     *     summary="Update a customer by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="customer",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active"),
     *             @OA\Property(property="password", type="string", example="NewPassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Customer updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Update Customer' permission"),
     *     @OA\Response(response=404, description="Customer not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function update(Request $request, User $customer): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $customer->id,
            'status' => 'nullable|in:active,inactive',
            'password' => 'nullable|string|min:8',
        ]);

        $customer = $this->adminCustomerService->updateCustomer($customer, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully',
            'data' => $customer,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/admin/customers/{customer}",
     *     tags={"Admin Customers"},
     *     summary="Delete a customer by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="customer",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Customer deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Missing 'Delete Customer' permission"),
     *     @OA\Response(response=404, description="Customer not found"),
     *     @OA\Response(response=500, description="Failed to delete customer")
     * )
     */
    public function destroy(User $customer): JsonResponse
    {
        try {
            $this->adminCustomerService->deleteCustomer($customer);

            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete customer: ' . $e->getMessage(),
            ], 500);
        }
    }
}
