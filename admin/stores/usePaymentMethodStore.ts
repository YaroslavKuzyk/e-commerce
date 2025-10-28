import { defineStore } from 'pinia';
import { usePaymentMethod } from '~/composables/usePaymentMethod';
import type { PaymentMethodFormData } from '~/models/paymentMethod';

export const usePaymentMethodStore = defineStore('paymentMethod', () => {
  const {
    getAllPaymentMethods,
    getPaymentMethodById,
    createPaymentMethod,
    updatePaymentMethod,
    togglePaymentMethodActive,
  } = usePaymentMethod();

  const fetchPaymentMethods = async () => {
    return await getAllPaymentMethods();
  };

  const fetchPaymentMethodById = async (id: number) => {
    return await getPaymentMethodById(id);
  };

  const onCreatePaymentMethod = async (payload: PaymentMethodFormData) => {
    return await createPaymentMethod(payload);
  };

  const onUpdatePaymentMethod = async (id: number, payload: PaymentMethodFormData) => {
    return await updatePaymentMethod(id, payload);
  };

  const onTogglePaymentMethodActive = async (id: number) => {
    return await togglePaymentMethodActive(id);
  };

  return {
    fetchPaymentMethods,
    fetchPaymentMethodById,
    onCreatePaymentMethod,
    onUpdatePaymentMethod,
    onTogglePaymentMethodActive,
  };
});
