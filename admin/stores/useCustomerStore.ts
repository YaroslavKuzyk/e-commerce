import { defineStore } from "pinia";
import { useCustomer } from "~/composables/useCustomer";
import type { ICustomerFilters } from "~/models/customers";

export const useCustomerStore = defineStore("customer", () => {
  const customerComposable = useCustomer();

  const fetchCustomers = (filters?: ICustomerFilters) => {
    return customerComposable.getAllCustomers(filters);
  };

  const fetchCustomerById = (id: number) => {
    return customerComposable.getCustomerById(id);
  };

  const onCreateCustomer = (payload: any) => {
    return customerComposable.createCustomer(payload);
  };

  const onUpdateCustomer = (payload: any) => {
    return customerComposable.updateCustomer(payload);
  };

  const onDeleteCustomer = (id: number) => {
    return customerComposable.deleteCustomer(id);
  };

  return {
    fetchCustomers,
    fetchCustomerById,
    onCreateCustomer,
    onUpdateCustomer,
    onDeleteCustomer,
  };
});
