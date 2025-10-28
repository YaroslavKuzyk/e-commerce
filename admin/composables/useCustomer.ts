import { CustomerService } from "~/services/CustomerService";
import type {
  ICustomerProvider,
  ICreateCustomer,
  IUpdateCustomer,
  ICustomerFilters,
} from "~/models/customers";

let provider: ICustomerProvider = new CustomerService();

export const useCustomer = () => {
  return {
    getAllCustomers: (filters?: ICustomerFilters) => provider.getAllCustomers(filters),
    getCustomerById: (id: number) => provider.getCustomerById(id),
    createCustomer: (payload: ICreateCustomer) => provider.createCustomer(payload),
    updateCustomer: (payload: IUpdateCustomer) => provider.updateCustomer(payload),
    deleteCustomer: (id: number) => provider.deleteCustomer(id),
  };
};
