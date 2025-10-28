<?php

namespace App\Services\Admin;

use App\Contracts\Services\Admin\AdminUserServiceInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Resources\UserResource;

class AdminUserService implements AdminUserServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function getAllAdmins(array $filters = [])
    {
        $query = User::query()->with(['roles'])->where('type', 'Admin');

        // Filter by search (name or email)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Filter by role
        if (!empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('roles.id', $filters['role']);
            });
        }

        // Filter by status (active/inactive)
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $admins = $query->get();

        return UserResource::collection($admins);
    }

    public function getAdminById(User $user)
    {
        $user->load(['roles.permissions']);

        return new UserResource($user);
    }

    public function createAdmin(array $data): User
    {
        // Hash the password
        $data['password'] = Hash::make($data['password']);

        // Set default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }

        // Set type as Admin
        $data['type'] = 'Admin';

        // Extract role_id for later
        $roleId = $data['role_id'] ?? null;
        unset($data['role_id']);

        // Create the user
        $user = $this->userRepository->create($data);

        // Assign role if provided
        if ($roleId) {
            $user->roles()->sync([$roleId]);
        }

        return $user->load(['roles.permissions']);
    }

    public function updateAdmin(User $user, array $data): User
    {
        // Prevent changing role of SuperAdmin users
        $user->load('roles');

        if ($user->hasRole('SuperAdmin')) {
            throw new \Exception('Неможливо редагувати роль адміністратора з роллю SuperAdmin');
        }

        // Handle password update - only if provided
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            // Remove password from data if not provided (don't update it)
            unset($data['password']);
        }

        // Extract role_id for later
        $roleId = $data['role_id'] ?? null;
        unset($data['role_id']);

        // Update the user
        $user = $this->userRepository->update($user, $data);

        // Update role if provided
        if ($roleId !== null) {
            $user->roles()->sync([$roleId]);
        }

        return $user->load(['roles.permissions']);
    }

    public function deleteAdmin(User $user): bool
    {
        // Prevent deletion of SuperAdmin users
        $user->load('roles');

        if ($user->hasRole('SuperAdmin')) {
            throw new \Exception('Неможливо видалити адміністратора з роллю SuperAdmin');
        }

        return $this->userRepository->delete($user);
    }
}
