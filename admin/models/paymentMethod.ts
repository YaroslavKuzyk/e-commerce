export interface IPaymentMethodProvider {
  getAllPaymentMethods(): ReturnType<typeof useAsyncData<PaymentMethod[] | undefined>>;
  getPaymentMethodById(id: number): ReturnType<typeof useAsyncData<PaymentMethod | undefined>>;
  createPaymentMethod(
    payload: PaymentMethodFormData
  ): ReturnType<typeof useAsyncData<PaymentMethod | undefined>>;
  updatePaymentMethod(
    id: number,
    payload: PaymentMethodFormData
  ): ReturnType<typeof useAsyncData<PaymentMethod | undefined>>;
  togglePaymentMethodActive(id: number): ReturnType<typeof useAsyncData<PaymentMethod | undefined>>;
}

export interface PaymentMethod {
  id: number;
  name: string;
  name_uk: string | null;
  code: string;
  description: string | null;
  description_uk: string | null;
  type: 'cash_on_delivery' | 'online';
  provider: string | null;
  provider_config: Record<string, any> | null;
  is_active: boolean;
  sort_order: number;
  pivot_is_active?: boolean;
  created_at: string;
  updated_at: string;
}

export interface PaymentMethodFormData {
  name: string;
  name_uk?: string | null;
  code: string;
  description?: string | null;
  description_uk?: string | null;
  type: 'cash_on_delivery' | 'online';
  provider?: string | null;
  provider_config?: Record<string, any> | null;
  is_active?: boolean;
  sort_order?: number;
}
