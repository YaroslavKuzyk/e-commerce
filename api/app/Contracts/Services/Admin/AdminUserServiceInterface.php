<?php

namespace App\Contracts\Services\Admin;

use App\Models\User;

interface AdminUserServiceInterface
{
    public function getAllAdmins(array $filters = []);

    public function getAdminById(User $user);

    public function createAdmin(array $data): User;

    public function updateAdmin(User $user, array $data): User;

    public function deleteAdmin(User $user): bool;
}
