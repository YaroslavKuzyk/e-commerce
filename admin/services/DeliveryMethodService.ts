import type {
  DeliveryMethod,
  DeliveryMethodFormData,
  IDeliveryMethodProvider,
} from '~/models/deliveryMethod';

export class DeliveryMethodService implements IDeliveryMethodProvider {
  getAllDeliveryMethods() {
    const client = useSanctumClient();

    return useAsyncData<DeliveryMethod[]>('delivery-methods', () =>
      client<{ success: boolean; data: DeliveryMethod[] }>(
        '/api/admin/delivery-methods'
      ).then((res) => res.data)
    );
  }

  getDeliveryMethodById(id: number) {
    const client = useSanctumClient();

    return useAsyncData<DeliveryMethod>(`delivery-method-${id}`, () =>
      client<{ success: boolean; data: DeliveryMethod }>(
        `/api/admin/delivery-methods/${id}`
      ).then((res) => res.data)
    );
  }

  createDeliveryMethod(payload: DeliveryMethodFormData) {
    const client = useSanctumClient();

    return useAsyncData<DeliveryMethod>(
      `delivery-method-create-${Date.now()}`,
      () =>
        client<{ success: boolean; data: DeliveryMethod }>(
          '/api/admin/delivery-methods',
          {
            method: 'POST',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  updateDeliveryMethod(id: number, payload: DeliveryMethodFormData) {
    const client = useSanctumClient();

    return useAsyncData<DeliveryMethod>(
      `delivery-method-update-${id}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: DeliveryMethod }>(
          `/api/admin/delivery-methods/${id}`,
          {
            method: 'PUT',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  toggleDeliveryMethodActive(id: number) {
    const client = useSanctumClient();

    return useAsyncData<DeliveryMethod>(
      `delivery-method-toggle-${id}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: DeliveryMethod }>(
          `/api/admin/delivery-methods/${id}/toggle-active`,
          {
            method: 'PATCH',
          }
        ).then((res) => res.data)
    );
  }

  syncPaymentMethods(id: number, paymentMethodIds: number[]) {
    const client = useSanctumClient();

    return useAsyncData<DeliveryMethod>(
      `delivery-method-sync-payment-${id}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: DeliveryMethod }>(
          `/api/admin/delivery-methods/${id}/payment-methods`,
          {
            method: 'POST',
            body: { payment_method_ids: paymentMethodIds },
          }
        ).then((res) => res.data)
    );
  }

  togglePaymentMethodActive(deliveryMethodId: number, paymentMethodId: number) {
    const client = useSanctumClient();

    return useAsyncData<void>(
      `delivery-method-toggle-payment-${deliveryMethodId}-${paymentMethodId}-${Date.now()}`,
      () =>
        client<{ success: boolean }>(
          `/api/admin/delivery-methods/${deliveryMethodId}/payment-methods/${paymentMethodId}/toggle-active`,
          {
            method: 'PATCH',
          }
        ).then(() => undefined)
    );
  }
}
