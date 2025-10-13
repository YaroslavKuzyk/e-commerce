<?php

namespace App\Http\Controllers;

use App\Contracts\Services\PermissionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionServiceInterface $permissionService
    ) {}

    public function index(): JsonResponse
    {
        $permissions = $this->permissionService->getAll();

        return response()->json([
            'success' => true,
            'data' => $permissions,
        ]);
    }
}
