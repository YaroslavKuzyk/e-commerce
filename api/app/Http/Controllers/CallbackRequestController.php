<?php

namespace App\Http\Controllers;

use App\Models\CallbackRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CallbackRequestController extends Controller
{
    /**
     * Store a new callback request
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:50',
            'name' => 'nullable|string|max:100',
            'comment' => 'nullable|string|max:1000',
        ]);

        $callbackRequest = CallbackRequest::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Callback request created successfully',
            'data' => $callbackRequest,
        ], 201);
    }
}
