<?php

namespace App\Contracts;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Collection;

interface AdminPaymentMethodServiceInterface
{
    /**
     * Get all payment methods.
     *
     * @return Collection
     */
    public function getAllPaymentMethods(): Collection;

    /**
     * Get a payment method by ID.
     *
     * @param int $id
     * @return PaymentMethod
     */
    public function getPaymentMethodById(int $id): PaymentMethod;

    /**
     * Create a new payment method.
     *
     * @param array $data
     * @return PaymentMethod
     */
    public function createPaymentMethod(array $data): PaymentMethod;

    /**
     * Update a payment method.
     *
     * @param int $id
     * @param array $data
     * @return PaymentMethod
     */
    public function updatePaymentMethod(int $id, array $data): PaymentMethod;

    /**
     * Toggle active status of a payment method.
     *
     * @param int $id
     * @return PaymentMethod
     */
    public function togglePaymentMethodActive(int $id): PaymentMethod;
}
