import type { PaymentMethod } from './paymentMethod';

export interface IDeliveryMethodProvider {
  getAllDeliveryMethods(): ReturnType<typeof useAsyncData<DeliveryMethod[] | undefined>>;
  getDeliveryMethodById(id: number): ReturnType<typeof useAsyncData<DeliveryMethod | undefined>>;
  createDeliveryMethod(
    payload: DeliveryMethodFormData
  ): ReturnType<typeof useAsyncData<DeliveryMethod | undefined>>;
  updateDeliveryMethod(
    id: number,
    payload: DeliveryMethodFormData
  ): ReturnType<typeof useAsyncData<DeliveryMethod | undefined>>;
  toggleDeliveryMethodActive(id: number): ReturnType<typeof useAsyncData<DeliveryMethod | undefined>>;
  syncPaymentMethods(
    id: number,
    paymentMethodIds: number[]
  ): ReturnType<typeof useAsyncData<DeliveryMethod | undefined>>;
  togglePaymentMethodActive(
    deliveryMethodId: number,
    paymentMethodId: number
  ): ReturnType<typeof useAsyncData<void | undefined>>;
}

export interface DeliveryMethod {
  id: number;
  name: string;
  name_uk: string | null;
  code: string;
  description: string | null;
  description_uk: string | null;
  has_api: boolean;
  api_config: Record<string, any> | null;
  is_active: boolean;
  sort_order: number;
  payment_methods?: PaymentMethod[];
  created_at: string;
  updated_at: string;
}

export interface DeliveryMethodFormData {
  name: string;
  name_uk?: string | null;
  code: string;
  description?: string | null;
  description_uk?: string | null;
  has_api?: boolean;
  api_config?: Record<string, any> | null;
  is_active?: boolean;
  sort_order?: number;
  payment_method_ids?: number[];
}
