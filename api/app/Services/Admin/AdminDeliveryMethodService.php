<?php

namespace App\Services\Admin;

use App\Contracts\AdminDeliveryMethodServiceInterface;
use App\Contracts\DeliveryMethodRepositoryInterface;
use App\Models\DeliveryMethod;
use Illuminate\Database\Eloquent\Collection;

class AdminDeliveryMethodService implements AdminDeliveryMethodServiceInterface
{
    public function __construct(
        protected DeliveryMethodRepositoryInterface $deliveryMethodRepository
    ) {}

    /**
     * Get all delivery methods.
     *
     * @return Collection
     */
    public function getAllDeliveryMethods(): Collection
    {
        return $this->deliveryMethodRepository->getAll();
    }

    /**
     * Get a delivery method by ID.
     *
     * @param int $id
     * @return DeliveryMethod
     * @throws \Exception
     */
    public function getDeliveryMethodById(int $id): DeliveryMethod
    {
        $deliveryMethod = $this->deliveryMethodRepository->findById($id);

        if (!$deliveryMethod) {
            throw new \Exception('Delivery method not found');
        }

        return $deliveryMethod;
    }

    /**
     * Create a new delivery method.
     *
     * @param array $data
     * @return DeliveryMethod
     */
    public function createDeliveryMethod(array $data): DeliveryMethod
    {
        $deliveryMethod = $this->deliveryMethodRepository->create($data);

        // Sync payment methods if provided
        if (isset($data['payment_method_ids']) && is_array($data['payment_method_ids'])) {
            $this->deliveryMethodRepository->syncPaymentMethods($deliveryMethod, $data['payment_method_ids']);
            $deliveryMethod = $deliveryMethod->fresh(['paymentMethods']);
        }

        return $deliveryMethod;
    }

    /**
     * Update a delivery method.
     *
     * @param int $id
     * @param array $data
     * @return DeliveryMethod
     * @throws \Exception
     */
    public function updateDeliveryMethod(int $id, array $data): DeliveryMethod
    {
        $deliveryMethod = $this->getDeliveryMethodById($id);

        $deliveryMethod = $this->deliveryMethodRepository->update($deliveryMethod, $data);

        // Sync payment methods if provided
        if (isset($data['payment_method_ids']) && is_array($data['payment_method_ids'])) {
            $this->deliveryMethodRepository->syncPaymentMethods($deliveryMethod, $data['payment_method_ids']);
            $deliveryMethod = $deliveryMethod->fresh(['paymentMethods']);
        }

        return $deliveryMethod;
    }

    /**
     * Toggle active status of a delivery method.
     *
     * @param int $id
     * @return DeliveryMethod
     * @throws \Exception
     */
    public function toggleDeliveryMethodActive(int $id): DeliveryMethod
    {
        $deliveryMethod = $this->getDeliveryMethodById($id);
        return $this->deliveryMethodRepository->toggleActive($deliveryMethod);
    }

    /**
     * Sync payment methods for a delivery method.
     *
     * @param int $id
     * @param array $paymentMethodIds
     * @return DeliveryMethod
     * @throws \Exception
     */
    public function syncPaymentMethods(int $id, array $paymentMethodIds): DeliveryMethod
    {
        $deliveryMethod = $this->getDeliveryMethodById($id);
        $this->deliveryMethodRepository->syncPaymentMethods($deliveryMethod, $paymentMethodIds);
        return $deliveryMethod->fresh(['paymentMethods']);
    }

    /**
     * Toggle payment method active status for a delivery method.
     *
     * @param int $deliveryMethodId
     * @param int $paymentMethodId
     * @return void
     * @throws \Exception
     */
    public function togglePaymentMethodActive(int $deliveryMethodId, int $paymentMethodId): void
    {
        $deliveryMethod = $this->getDeliveryMethodById($deliveryMethodId);
        $this->deliveryMethodRepository->togglePaymentMethodActive($deliveryMethod, $paymentMethodId);
    }
}
