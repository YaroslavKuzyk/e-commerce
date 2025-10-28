import type {
  PaymentMethod,
  PaymentMethodFormData,
  IPaymentMethodProvider,
} from '~/models/paymentMethod';

export class PaymentMethodService implements IPaymentMethodProvider {
  getAllPaymentMethods() {
    const client = useSanctumClient();

    return useAsyncData<PaymentMethod[]>('payment-methods', () =>
      client<{ success: boolean; data: PaymentMethod[] }>(
        '/api/admin/payment-methods'
      ).then((res) => res.data)
    );
  }

  getPaymentMethodById(id: number) {
    const client = useSanctumClient();

    return useAsyncData<PaymentMethod>(`payment-method-${id}`, () =>
      client<{ success: boolean; data: PaymentMethod }>(
        `/api/admin/payment-methods/${id}`
      ).then((res) => res.data)
    );
  }

  createPaymentMethod(payload: PaymentMethodFormData) {
    const client = useSanctumClient();

    return useAsyncData<PaymentMethod>(
      `payment-method-create-${Date.now()}`,
      () =>
        client<{ success: boolean; data: PaymentMethod }>(
          '/api/admin/payment-methods',
          {
            method: 'POST',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  updatePaymentMethod(id: number, payload: PaymentMethodFormData) {
    const client = useSanctumClient();

    return useAsyncData<PaymentMethod>(
      `payment-method-update-${id}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: PaymentMethod }>(
          `/api/admin/payment-methods/${id}`,
          {
            method: 'PUT',
            body: payload,
          }
        ).then((res) => res.data)
    );
  }

  togglePaymentMethodActive(id: number) {
    const client = useSanctumClient();

    return useAsyncData<PaymentMethod>(
      `payment-method-toggle-${id}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: PaymentMethod }>(
          `/api/admin/payment-methods/${id}/toggle-active`,
          {
            method: 'PATCH',
          }
        ).then((res) => res.data)
    );
  }
}
