<?php

namespace App\Contracts\Repositories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Collection;

interface PaymentMethodRepositoryInterface
{
    /**
     * Get all payment methods.
     *
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * Get only active payment methods.
     *
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Find a payment method by ID.
     *
     * @param int $id
     * @return PaymentMethod|null
     */
    public function findById(int $id): ?PaymentMethod;

    /**
     * Find a payment method by code.
     *
     * @param string $code
     * @return PaymentMethod|null
     */
    public function findByCode(string $code): ?PaymentMethod;

    /**
     * Create a new payment method.
     *
     * @param array $data
     * @return PaymentMethod
     */
    public function create(array $data): PaymentMethod;

    /**
     * Update a payment method.
     *
     * @param PaymentMethod $paymentMethod
     * @param array $data
     * @return PaymentMethod
     */
    public function update(PaymentMethod $paymentMethod, array $data): PaymentMethod;

    /**
     * Toggle active status of a payment method.
     *
     * @param PaymentMethod $paymentMethod
     * @return PaymentMethod
     */
    public function toggleActive(PaymentMethod $paymentMethod): PaymentMethod;
}
