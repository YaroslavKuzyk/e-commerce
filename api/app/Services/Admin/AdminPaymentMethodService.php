<?php

namespace App\Services\Admin;

use App\Contracts\AdminPaymentMethodServiceInterface;
use App\Contracts\PaymentMethodRepositoryInterface;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Collection;

class AdminPaymentMethodService implements AdminPaymentMethodServiceInterface
{
    public function __construct(
        protected PaymentMethodRepositoryInterface $paymentMethodRepository
    ) {}

    /**
     * Get all payment methods.
     *
     * @return Collection
     */
    public function getAllPaymentMethods(): Collection
    {
        return $this->paymentMethodRepository->getAll();
    }

    /**
     * Get a payment method by ID.
     *
     * @param int $id
     * @return PaymentMethod
     * @throws \Exception
     */
    public function getPaymentMethodById(int $id): PaymentMethod
    {
        $paymentMethod = $this->paymentMethodRepository->findById($id);

        if (!$paymentMethod) {
            throw new \Exception('Payment method not found');
        }

        return $paymentMethod;
    }

    /**
     * Create a new payment method.
     *
     * @param array $data
     * @return PaymentMethod
     */
    public function createPaymentMethod(array $data): PaymentMethod
    {
        return $this->paymentMethodRepository->create($data);
    }

    /**
     * Update a payment method.
     *
     * @param int $id
     * @param array $data
     * @return PaymentMethod
     * @throws \Exception
     */
    public function updatePaymentMethod(int $id, array $data): PaymentMethod
    {
        $paymentMethod = $this->getPaymentMethodById($id);
        return $this->paymentMethodRepository->update($paymentMethod, $data);
    }

    /**
     * Toggle active status of a payment method.
     *
     * @param int $id
     * @return PaymentMethod
     * @throws \Exception
     */
    public function togglePaymentMethodActive(int $id): PaymentMethod
    {
        $paymentMethod = $this->getPaymentMethodById($id);
        return $this->paymentMethodRepository->toggleActive($paymentMethod);
    }
}
