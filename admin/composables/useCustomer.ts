import { CustomerService } from "~/services/CustomerService";
import type { ICustomerProvider } from "~/models/customers";

export const useCustomer = (): ICustomerProvider => {
  const customerService = new CustomerService();

  return {
    getAllCustomers: customerService.getAllCustomers.bind(customerService),
    getCustomerById: customerService.getCustomerById.bind(customerService),
    createCustomer: customerService.createCustomer.bind(customerService),
    updateCustomer: customerService.updateCustomer.bind(customerService),
    deleteCustomer: customerService.deleteCustomer.bind(customerService),
  };
};
