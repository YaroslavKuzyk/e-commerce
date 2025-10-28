<?php

namespace App\Repositories;

use App\Contracts\PaymentMethodRepositoryInterface;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Collection;

class PaymentMethodRepository implements PaymentMethodRepositoryInterface
{
    /**
     * Get all payment methods.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return PaymentMethod::ordered()->get();
    }

    /**
     * Get only active payment methods.
     *
     * @return Collection
     */
    public function getActive(): Collection
    {
        return PaymentMethod::active()->ordered()->get();
    }

    /**
     * Find a payment method by ID.
     *
     * @param int $id
     * @return PaymentMethod|null
     */
    public function findById(int $id): ?PaymentMethod
    {
        return PaymentMethod::find($id);
    }

    /**
     * Find a payment method by code.
     *
     * @param string $code
     * @return PaymentMethod|null
     */
    public function findByCode(string $code): ?PaymentMethod
    {
        return PaymentMethod::where('code', $code)->first();
    }

    /**
     * Create a new payment method.
     *
     * @param array $data
     * @return PaymentMethod
     */
    public function create(array $data): PaymentMethod
    {
        return PaymentMethod::create($data);
    }

    /**
     * Update a payment method.
     *
     * @param PaymentMethod $paymentMethod
     * @param array $data
     * @return PaymentMethod
     */
    public function update(PaymentMethod $paymentMethod, array $data): PaymentMethod
    {
        $paymentMethod->update($data);
        return $paymentMethod->fresh();
    }

    /**
     * Toggle active status of a payment method.
     *
     * @param PaymentMethod $paymentMethod
     * @return PaymentMethod
     */
    public function toggleActive(PaymentMethod $paymentMethod): PaymentMethod
    {
        $paymentMethod->update(['is_active' => !$paymentMethod->is_active]);
        return $paymentMethod->fresh();
    }
}
