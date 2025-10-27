<?php

namespace App\Services;

use App\Actions\Role\ReassignUsersToRoleAction;
use App\Contracts\Services\RoleServiceInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\RoleResource;

class RoleService implements RoleServiceInterface
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private ReassignUsersToRoleAction $reassignUsersAction
    ) {}

    public function getAllRoles()
    {
        $roles = $this->roleRepository->getAll();
        return RoleResource::collection($roles);
    }

    public function getRoleById(Role $role)
    {
        $role->load(['permissions', 'users']);
        $role->loadCount(['users', 'permissions']);

        return $role;
    }

    public function createRole(array $data): Role
    {
        $permissions = $data['permissions'] ?? [];
        unset($data['permissions']);

        $role = $this->roleRepository->create($data);

        if (!empty($permissions)) {
            $role->permissions()->sync($permissions);
        }

        return $role->load('permissions');
    }

    public function updateRole(Role $role, array $data): Role
    {
        $permissions = $data['permissions'] ?? null;
        unset($data['permissions']);

        $role = $this->roleRepository->update($role, $data);

        if ($permissions !== null) {
            $role->permissions()->sync($permissions);
        }

        return $role->load('permissions');
    }

    public function deleteRoleWithReassignment(Role $role, int $replacementRoleId): array
    {
        $replacementRole = $this->roleRepository->findByIdOrFail($replacementRoleId);
        $users = $this->roleRepository->getUsersWithRole($role);

        DB::beginTransaction();

        try {
            $reassignedCount = $this->reassignUsersAction->execute($users, $role, $replacementRole);
            $this->roleRepository->delete($role);
            DB::commit();

            return [
                'success' => true,
                'reassigned_count' => $reassignedCount,
                'replacement_role' => $replacementRole->name,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
