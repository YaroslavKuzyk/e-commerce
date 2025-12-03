<?php

namespace App\Contracts\Services;

use App\Models\User;

interface CustomerAuthServiceInterface
{
    public function register(array $data): array;

    public function login(array $credentials): string;

    public function logout(User $user): void;
}
