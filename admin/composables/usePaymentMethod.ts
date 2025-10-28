import { PaymentMethodService } from "~/services/PaymentMethodService";
import type {
  IPaymentMethodProvider,
  PaymentMethodFormData,
} from "~/models/paymentMethod";

let provider: IPaymentMethodProvider = new PaymentMethodService();

export const usePaymentMethod = () => {
  return {
    getAllPaymentMethods: () => provider.getAllPaymentMethods(),
    getPaymentMethodById: (id: number) => provider.getPaymentMethodById(id),
    createPaymentMethod: (payload: PaymentMethodFormData) =>
      provider.createPaymentMethod(payload),
    updatePaymentMethod: (id: number, payload: PaymentMethodFormData) =>
      provider.updatePaymentMethod(id, payload),
    togglePaymentMethodActive: (id: number) =>
      provider.togglePaymentMethodActive(id),
  };
};
