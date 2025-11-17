import { defineStore } from "pinia";
import { useCustomer } from "~/composables/useCustomer";
import type { ICustomerFilters } from "~/models/customers";

export const useCustomerStore = defineStore("customer", () => {
  const {
    getAllCustomers,
    getAllCustomersPromise,
    getCustomerById,
    createCustomer,
    updateCustomer,
    deleteCustomer,
  } = useCustomer();

  const fetchCustomers = (filters?: ICustomerFilters) => {
    return getAllCustomers(filters);
  };

  const fetchCustomersPromise = async (filters?: ICustomerFilters) => {
    return await getAllCustomersPromise(filters);
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
    fetchCustomersPromise,
    fetchCustomerById,
    onCreateCustomer,
    onUpdateCustomer,
    onDeleteCustomer,
  };
});
