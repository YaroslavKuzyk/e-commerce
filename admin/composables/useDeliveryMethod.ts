import { DeliveryMethodService } from "~/services/DeliveryMethodService";
import type {
  IDeliveryMethodProvider,
  DeliveryMethodFormData,
} from "~/models/deliveryMethod";

let provider: IDeliveryMethodProvider = new DeliveryMethodService();

export const useDeliveryMethod = () => {
  return {
    getAllDeliveryMethods: () => provider.getAllDeliveryMethods(),
    getDeliveryMethodById: (id: number) => provider.getDeliveryMethodById(id),
    createDeliveryMethod: (payload: DeliveryMethodFormData) =>
      provider.createDeliveryMethod(payload),
    updateDeliveryMethod: (id: number, payload: DeliveryMethodFormData) =>
      provider.updateDeliveryMethod(id, payload),
    toggleDeliveryMethodActive: (id: number) =>
      provider.toggleDeliveryMethodActive(id),
    syncPaymentMethods: (id: number, paymentMethodIds: number[]) =>
      provider.syncPaymentMethods(id, paymentMethodIds),
    togglePaymentMethodActive: (deliveryMethodId: number, paymentMethodId: number) =>
      provider.togglePaymentMethodActive(deliveryMethodId, paymentMethodId),
  };
};
