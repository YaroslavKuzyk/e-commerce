<?php

namespace App\Contracts\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function create(array $data): User;

    public function findById(int $id): ?User;

    public function update(User $user, array $data): User;

    public function delete(User $user): bool;
}
