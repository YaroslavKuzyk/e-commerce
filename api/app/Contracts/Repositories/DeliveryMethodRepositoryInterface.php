<?php

namespace App\Contracts\Repositories;

use App\Models\DeliveryMethod;
use Illuminate\Database\Eloquent\Collection;

interface DeliveryMethodRepositoryInterface
{
    /**
     * Get all delivery methods.
     *
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * Get only active delivery methods.
     *
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Find a delivery method by ID.
     *
     * @param int $id
     * @return DeliveryMethod|null
     */
    public function findById(int $id): ?DeliveryMethod;

    /**
     * Find a delivery method by code.
     *
     * @param string $code
     * @return DeliveryMethod|null
     */
    public function findByCode(string $code): ?DeliveryMethod;

    /**
     * Create a new delivery method.
     *
     * @param array $data
     * @return DeliveryMethod
     */
    public function create(array $data): DeliveryMethod;

    /**
     * Update a delivery method.
     *
     * @param DeliveryMethod $deliveryMethod
     * @param array $data
     * @return DeliveryMethod
     */
    public function update(DeliveryMethod $deliveryMethod, array $data): DeliveryMethod;

    /**
     * Toggle active status of a delivery method.
     *
     * @param DeliveryMethod $deliveryMethod
     * @return DeliveryMethod
     */
    public function toggleActive(DeliveryMethod $deliveryMethod): DeliveryMethod;

    /**
     * Attach payment methods to a delivery method.
     *
     * @param DeliveryMethod $deliveryMethod
     * @param array $paymentMethodIds
     * @return void
     */
    public function syncPaymentMethods(DeliveryMethod $deliveryMethod, array $paymentMethodIds): void;

    /**
     * Toggle active status of a payment method for a delivery method.
     *
     * @param DeliveryMethod $deliveryMethod
     * @param int $paymentMethodId
     * @return void
     */
    public function togglePaymentMethodActive(DeliveryMethod $deliveryMethod, int $paymentMethodId): void;
}
