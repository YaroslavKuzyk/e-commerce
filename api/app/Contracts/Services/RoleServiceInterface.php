<?php

namespace App\Contracts\Services;

use App\Models\Role;

interface RoleServiceInterface
{
    public function getAllRoles();

    public function getRoleById(Role $role);

    public function createRole(array $data): Role;

    public function updateRole(Role $role, array $data): Role;

    public function deleteRoleWithReassignment(Role $role, int $replacementRoleId): array;
}
