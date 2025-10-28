<?php

namespace App\Contracts;

use App\Models\DeliveryMethod;
use Illuminate\Database\Eloquent\Collection;

interface AdminDeliveryMethodServiceInterface
{
    /**
     * Get all delivery methods.
     *
     * @return Collection
     */
    public function getAllDeliveryMethods(): Collection;

    /**
     * Get a delivery method by ID.
     *
     * @param int $id
     * @return DeliveryMethod
     */
    public function getDeliveryMethodById(int $id): DeliveryMethod;

    /**
     * Create a new delivery method.
     *
     * @param array $data
     * @return DeliveryMethod
     */
    public function createDeliveryMethod(array $data): DeliveryMethod;

    /**
     * Update a delivery method.
     *
     * @param int $id
     * @param array $data
     * @return DeliveryMethod
     */
    public function updateDeliveryMethod(int $id, array $data): DeliveryMethod;

    /**
     * Toggle active status of a delivery method.
     *
     * @param int $id
     * @return DeliveryMethod
     */
    public function toggleDeliveryMethodActive(int $id): DeliveryMethod;

    /**
     * Sync payment methods for a delivery method.
     *
     * @param int $id
     * @param array $paymentMethodIds
     * @return DeliveryMethod
     */
    public function syncPaymentMethods(int $id, array $paymentMethodIds): DeliveryMethod;

    /**
     * Toggle payment method active status for a delivery method.
     *
     * @param int $deliveryMethodId
     * @param int $paymentMethodId
     * @return void
     */
    public function togglePaymentMethodActive(int $deliveryMethodId, int $paymentMethodId): void;
}
