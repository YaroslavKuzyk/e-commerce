<?php

namespace App\Repositories;

use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository implements RoleRepositoryInterface
{
    public function getAll(): Collection
    {
        $roles = Role::with('permissions')->withCount(['users', 'permissions'])->get();
        // return $roles->filter(function ($role) {
        //     return $role->name !== 'SuperAdmin';
        // });
        return $roles;
    }

    public function findById(int $id): ?Role
    {
        return Role::find($id);
    }

    public function findByIdOrFail(int $id): Role
    {
        return Role::findOrFail($id);
    }

    public function create(array $data): Role
    {
        return Role::create($data);
    }

    public function update(Role $role, array $data): Role
    {
        $role->update($data);
        return $role->fresh();
    }

    public function delete(Role $role): bool
    {
        return $role->delete();
    }

    public function getUsersWithRole(Role $role): Collection
    {
        return $role->users;
    }
}
