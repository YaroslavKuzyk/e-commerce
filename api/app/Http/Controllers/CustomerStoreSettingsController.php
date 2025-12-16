<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use Illuminate\Http\JsonResponse;

class CustomerStoreSettingsController extends Controller
{
    /**
     * Get public store settings
     *
     * @OA\Get(
     *     path="/api/store-settings",
     *     tags={"Store Settings"},
     *     summary="Get public store settings",
     *     @OA\Response(
     *         response=200,
     *         description="Store settings",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     * )
     */
    public function index(): JsonResponse
    {
        $settings = StoreSetting::getAllSettings();
        $defaults = StoreSetting::getDefaults();

        // Merge with defaults to ensure all keys exist
        $result = array_replace_recursive($defaults, $settings);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
