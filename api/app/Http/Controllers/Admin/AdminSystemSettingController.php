<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\AdminSystemSettingServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\SystemSettingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSystemSettingController extends Controller
{
    public function __construct(
        private AdminSystemSettingServiceInterface $service
    ) {}

    /**
     * Get all system settings with available types
     */
    public function index(): JsonResponse
    {
        $types = $this->service->getAvailableTypes();

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    /**
     * Get a specific system setting by type
     */
    public function show(string $type): JsonResponse
    {
        $setting = $this->service->getSettingByType($type);

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'System setting not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new SystemSettingResource($setting),
        ]);
    }

    /**
     * Update or create a system setting
     */
    public function update(Request $request, string $type): JsonResponse
    {
        $validated = $request->validate([
            'data' => 'required|array',
            'is_active' => 'sometimes|boolean',
        ]);

        try {
            $setting = $this->service->updateSetting($type, $validated);

            return response()->json([
                'success' => true,
                'message' => 'System setting updated successfully',
                'data' => new SystemSettingResource($setting),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Toggle system setting active status
     */
    public function toggleActive(Request $request, string $type): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        try {
            $setting = $this->service->toggleActive($type, $validated['is_active']);

            return response()->json([
                'success' => true,
                'message' => 'System setting status updated',
                'data' => new SystemSettingResource($setting),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete a system setting (reset to defaults)
     */
    public function destroy(string $type): JsonResponse
    {
        $deleted = $this->service->deleteSetting($type);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'System setting not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'System setting deleted successfully',
        ]);
    }
}
