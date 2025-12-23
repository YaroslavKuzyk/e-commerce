<?php

namespace App\Repositories;

use App\Contracts\Repositories\DeliveryMethodRepositoryInterface;
use App\Models\DeliveryMethod;
use Illuminate\Database\Eloquent\Collection;

class DeliveryMethodRepository implements DeliveryMethodRepositoryInterface
{
    /**
     * Get all delivery methods.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return DeliveryMethod::with('paymentMethods')->ordered()->get();
    }

    /**
     * Get only active delivery methods.
     *
     * @return Collection
     */
    public function getActive(): Collection
    {
        return DeliveryMethod::active()
            ->with(['paymentMethods' => function ($query) {
                $query->wherePivot('is_active', true)->where('is_active', true);
            }])
            ->ordered()
            ->get();
    }

    /**
     * Find a delivery method by ID.
     *
     * @param int $id
     * @return DeliveryMethod|null
     */
    public function findById(int $id): ?DeliveryMethod
    {
        return DeliveryMethod::with('paymentMethods')->find($id);
    }

    /**
     * Find a delivery method by code.
     *
     * @param string $code
     * @return DeliveryMethod|null
     */
    public function findByCode(string $code): ?DeliveryMethod
    {
        return DeliveryMethod::where('code', $code)->first();
    }

    /**
     * Create a new delivery method.
     *
     * @param array $data
     * @return DeliveryMethod
     */
    public function create(array $data): DeliveryMethod
    {
        return DeliveryMethod::create($data);
    }

    /**
     * Update a delivery method.
     *
     * @param DeliveryMethod $deliveryMethod
     * @param array $data
     * @return DeliveryMethod
     */
    public function update(DeliveryMethod $deliveryMethod, array $data): DeliveryMethod
    {
        $deliveryMethod->update($data);
        return $deliveryMethod->fresh(['paymentMethods']);
    }

    /**
     * Toggle active status of a delivery method.
     *
     * @param DeliveryMethod $deliveryMethod
     * @return DeliveryMethod
     */
    public function toggleActive(DeliveryMethod $deliveryMethod): DeliveryMethod
    {
        $deliveryMethod->update(['is_active' => !$deliveryMethod->is_active]);
        return $deliveryMethod->fresh();
    }

    /**
     * Attach payment methods to a delivery method.
     *
     * @param DeliveryMethod $deliveryMethod
     * @param array $paymentMethodIds
     * @return void
     */
    public function syncPaymentMethods(DeliveryMethod $deliveryMethod, array $paymentMethodIds): void
    {
        $syncData = [];
        foreach ($paymentMethodIds as $paymentMethodId) {
            $syncData[$paymentMethodId] = ['is_active' => true];
        }
        $deliveryMethod->paymentMethods()->sync($syncData);
    }

    /**
     * Toggle active status of a payment method for a delivery method.
     *
     * @param DeliveryMethod $deliveryMethod
     * @param int $paymentMethodId
     * @return void
     */
    public function togglePaymentMethodActive(DeliveryMethod $deliveryMethod, int $paymentMethodId): void
    {
        $pivot = $deliveryMethod->paymentMethods()->where('payment_method_id', $paymentMethodId)->first();

        if ($pivot) {
            $deliveryMethod->paymentMethods()->updateExistingPivot(
                $paymentMethodId,
                ['is_active' => !$pivot->pivot->is_active]
            );
        }
    }
}
