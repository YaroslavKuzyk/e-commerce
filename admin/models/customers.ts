import type { IPaginationMeta } from "~/composables/usePaginationList";

export interface ICustomerProvider {
  getAllCustomers(
    filters?: ICustomerFilters
  ): ReturnType<typeof useAsyncData<ICustomer[] | undefined>>;
  getAllCustomersPromise(filters?: ICustomerFilters): Promise<ICustomersListResponse>;
  getCustomerById(id: number): ReturnType<typeof useAsyncData<ICustomer | undefined>>;
  createCustomer(
    payload: ICreateCustomer
  ): ReturnType<typeof useAsyncData<ICustomer | undefined>>;
  updateCustomer(
    payload: IUpdateCustomer
  ): ReturnType<typeof useAsyncData<ICustomer | undefined>>;
  deleteCustomer(id: number): ReturnType<typeof useAsyncData<void | undefined>>;
}

export interface ICustomer {
  id: number;
  name: string;
  email: string;
  status: "active" | "inactive";
  created_at: string;
  updated_at: string;
}

export interface ICustomerFilters {
  search?: string;
  status?: "active" | "inactive" | null;
  page?: number;
  per_page?: number;
}

export interface ICustomersListResponse {
  success: boolean;
  data: ICustomer[];
  meta?: IPaginationMeta;
}

export interface ICreateCustomer {
  name: string;
  email: string;
  status?: "active" | "inactive";
  password?: string;
}

export interface IUpdateCustomer {
  customerId: number;
  name: string;
  email: string;
  status?: "active" | "inactive";
  password?: string;
}
