import { defineStore } from 'pinia';
import { useDeliveryMethod } from '~/composables/useDeliveryMethod';
import type { DeliveryMethodFormData } from '~/models/deliveryMethod';

export const useDeliveryMethodStore = defineStore('deliveryMethod', () => {
  const {
    getAllDeliveryMethods,
    getDeliveryMethodById,
    createDeliveryMethod,
    updateDeliveryMethod,
    toggleDeliveryMethodActive,
    syncPaymentMethods,
    togglePaymentMethodActive,
  } = useDeliveryMethod();

  const fetchDeliveryMethods = async () => {
    return await getAllDeliveryMethods();
  };

  const fetchDeliveryMethodById = async (id: number) => {
    return await getDeliveryMethodById(id);
  };

  const onCreateDeliveryMethod = async (payload: DeliveryMethodFormData) => {
    return await createDeliveryMethod(payload);
  };

  const onUpdateDeliveryMethod = async (id: number, payload: DeliveryMethodFormData) => {
    return await updateDeliveryMethod(id, payload);
  };

  const onToggleDeliveryMethodActive = async (id: number) => {
    return await toggleDeliveryMethodActive(id);
  };

  const onSyncPaymentMethods = async (id: number, paymentMethodIds: number[]) => {
    return await syncPaymentMethods(id, paymentMethodIds);
  };

  const onTogglePaymentMethodActive = async (deliveryMethodId: number, paymentMethodId: number) => {
    return await togglePaymentMethodActive(deliveryMethodId, paymentMethodId);
  };

  return {
    fetchDeliveryMethods,
    fetchDeliveryMethodById,
    onCreateDeliveryMethod,
    onUpdateDeliveryMethod,
    onToggleDeliveryMethodActive,
    onSyncPaymentMethods,
    onTogglePaymentMethodActive,
  };
});
