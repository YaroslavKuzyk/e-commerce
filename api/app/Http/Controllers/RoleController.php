<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $roles = Role::withCount(['users', 'permissions'])->get();

        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
        ]);

        $role = Role::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => $role,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): JsonResponse
    {
        $role->load(['permissions', 'users']);
        $role->loadCount(['users', 'permissions']);

        return response()->json([
            'success' => true,
            'data' => $role,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:500',
        ]);

        $role->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => $role->fresh(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * Reassigns users from deleted role to a replacement role.
     */
    public function destroy(Request $request, Role $role): JsonResponse
    {
        // Validate replacement role
        $validated = $request->validate([
            'replacement_role_id' => 'required|exists:roles,id|different:' . $role->id,
        ]);

        $replacementRole = Role::findOrFail($validated['replacement_role_id']);

        // Get all users with this role
        $users = $role->users;

        // Start transaction
        \DB::beginTransaction();

        try {
            // Reassign all users to the replacement role
            foreach ($users as $user) {
                $user->roles()->detach($role->id);
                $user->assignRole($replacementRole);
            }

            // Delete the role
            $role->delete();

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Role deleted successfully. {$users->count()} users reassigned to '{$replacementRole->name}'",
                'reassigned_users_count' => $users->count(),
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role: ' . $e->getMessage(),
            ], 500);
        }
    }
}
