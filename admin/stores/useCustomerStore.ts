import { defineStore } from "pinia";
import { useCustomer } from "~/composables/useCustomer";
import type { ICustomerFilters } from "~/models/customers";

export const useCustomerStore = defineStore("customer", () => {
  const {
    getAllCustomers,
    getCustomerById,
    createCustomer,
    updateCustomer,
    deleteCustomer,
  } = useCustomer();

  const fetchCustomers = (filters?: ICustomerFilters) => {
    return getAllCustomers(filters);
  };

  const fetchCustomerById = (id: number) => {
    return getCustomerById(id);
  };

  const onCreateCustomer = async (payload: any) => {
    return await createCustomer(payload);
  };

  const onUpdateCustomer = async (payload: any) => {
    return await updateCustomer(payload);
  };

  const onDeleteCustomer = async (id: number) => {
    return await deleteCustomer(id);
  };

  return {
    fetchCustomers,
    fetchCustomerById,
    onCreateCustomer,
    onUpdateCustomer,
    onDeleteCustomer,
  };
});
