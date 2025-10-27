<?php

namespace App\Contracts\Services\Admin;

use App\Models\Role;

interface AdminRoleServiceInterface
{
    public function getAllRoles();

    public function getRoleById(Role $role);

    public function createRole(array $data): Role;

    public function updateRole(Role $role, array $data): Role;

    public function deleteRoleWithReassignment(Role $role, int $replacementRoleId): array;
}
