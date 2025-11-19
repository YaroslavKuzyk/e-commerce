<?php

namespace App\Services\Admin;

use App\Contracts\Services\Admin\AdminCustomerServiceInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Resources\UserResource;
use App\Enums\UserType;

class AdminCustomerService implements AdminCustomerServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function getAllCustomers(array $filters = [])
    {
        $query = User::query()->where('type', UserType::CUSTOMER);

        // Filter by search (name or email)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Filter by status (active/inactive)
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply pagination if per_page is provided
        if (!empty($filters['per_page'])) {
            $customers = $query->paginate(
                $filters['per_page'],
                ['*'],
                'page',
                $filters['page'] ?? 1
            );
            return UserResource::collection($customers);
        }

        $customers = $query->get();

        return UserResource::collection($customers);
    }

    public function getCustomerById(User $user)
    {
        return new UserResource($user);
    }

    public function createCustomer(array $data): User
    {
        // Hash the password
        $data['password'] = Hash::make($data['password']);

        // Set default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }

        // Set type as Customer
        $data['type'] = UserType::CUSTOMER;

        // Create the user
        $user = $this->userRepository->create($data);

        return $user;
    }

    public function updateCustomer(User $user, array $data): User
    {
        // Handle password update - only if provided
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            // Remove password from data if not provided (don't update it)
            unset($data['password']);
        }

        // Ensure type remains Customer
        $data['type'] = UserType::CUSTOMER;

        // Update the user
        $user = $this->userRepository->update($user, $data);

        return $user;
    }

    public function deleteCustomer(User $user): bool
    {
        return $this->userRepository->delete($user);
    }
}
