<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\Services\Admin\AdminPermissionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPermissionController extends Controller
{
    public function __construct(
        private readonly AdminPermissionServiceInterface $permissionService
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/admin/permissions",
     *     tags={"Admin Permissions"},
     *     summary="Get all permissions",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Read Users"),
     *                 @OA\Property(property="type", type="string", example="read"),
     *                 @OA\Property(property="group", type="string", example="users")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="User is authenticated, but dont have permission **Read Permissions** to access this resource"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function index(): JsonResponse
    {
        $permissions = $this->permissionService->getAll();

        return response()->json([
            'success' => true,
            'data' => $permissions,
        ]);
    }
}
