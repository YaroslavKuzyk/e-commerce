<?php

namespace App\Contracts\Services\Admin;

use App\Models\User;

interface AdminAuthServiceInterface
{
    public function register(array $data): array;

    public function login(array $credentials): string;

    public function logout(User $user): void;
}
