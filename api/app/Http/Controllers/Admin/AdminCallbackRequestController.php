<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CallbackRequestResource;
use App\Models\CallbackRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminCallbackRequestController extends Controller
{
    /**
     * Get all callback requests with pagination
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = CallbackRequest::query();

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by phone or name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('phone', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        return CallbackRequestResource::collection($query->paginate($perPage));
    }

    /**
     * Get callback request stats
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total' => CallbackRequest::count(),
            'new' => CallbackRequest::where('status', CallbackRequest::STATUS_NEW)->count(),
            'in_progress' => CallbackRequest::where('status', CallbackRequest::STATUS_IN_PROGRESS)->count(),
            'completed' => CallbackRequest::where('status', CallbackRequest::STATUS_COMPLETED)->count(),
            'cancelled' => CallbackRequest::where('status', CallbackRequest::STATUS_CANCELLED)->count(),
        ];

        return response()->json(['data' => $stats]);
    }

    /**
     * Get a single callback request
     */
    public function show(int $id): CallbackRequestResource
    {
        $callbackRequest = CallbackRequest::findOrFail($id);
        return new CallbackRequestResource($callbackRequest);
    }

    /**
     * Update callback request status
     */
    public function update(Request $request, int $id): CallbackRequestResource
    {
        $callbackRequest = CallbackRequest::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:new,in_progress,completed,cancelled',
        ]);

        $callbackRequest->update($validated);

        return new CallbackRequestResource($callbackRequest);
    }

    /**
     * Delete a callback request
     */
    public function destroy(int $id): JsonResponse
    {
        $callbackRequest = CallbackRequest::findOrFail($id);
        $callbackRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Callback request deleted successfully',
        ]);
    }

    /**
     * Bulk delete callback requests
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:callback_requests,id',
        ]);

        CallbackRequest::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'success' => true,
            'message' => 'Callback requests deleted successfully',
        ]);
    }

    /**
     * Bulk update callback requests status
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:callback_requests,id',
            'status' => 'required|in:new,in_progress,completed,cancelled',
        ]);

        CallbackRequest::whereIn('id', $validated['ids'])->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Callback requests status updated successfully',
        ]);
    }
}
