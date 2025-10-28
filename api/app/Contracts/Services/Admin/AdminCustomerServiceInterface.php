<?php

namespace App\Contracts\Services\Admin;

use App\Models\User;

interface AdminCustomerServiceInterface
{
    public function getAllCustomers(array $filters = []);
    public function getCustomerById(User $user);
    public function createCustomer(array $data): User;
    public function updateCustomer(User $user, array $data): User;
    public function deleteCustomer(User $user): bool;
}
