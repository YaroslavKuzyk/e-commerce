import type {
  ICustomer,
  ICustomerProvider,
  ICreateCustomer,
  IUpdateCustomer,
  ICustomerFilters,
} from "~/models/customers";

export class CustomerService implements ICustomerProvider {
  getAllCustomers(filters?: ICustomerFilters) {
    const client = useSanctumClient();

    const queryParams = new URLSearchParams();
    if (filters?.search) queryParams.append("search", filters.search);
    if (filters?.status) queryParams.append("status", filters.status);

    const queryString = queryParams.toString();
    const url = queryString
      ? `/api/admin/customers?${queryString}`
      : "/api/admin/customers";

    return useAsyncData<ICustomer[]>(
      `customers-${queryString}`,
      () =>
        client<{ success: boolean; data: ICustomer[] }>(url).then(
          (res) => res.data
        )
    );
  }

  getCustomerById(id: number) {
    const client = useSanctumClient();

    return useAsyncData<ICustomer>(`customer-${id}`, () =>
      client<{ success: boolean; data: ICustomer }>(
        `/api/admin/customers/${id}`
      ).then((res) => res.data)
    );
  }

  createCustomer(payload: ICreateCustomer) {
    const client = useSanctumClient();

    return useAsyncData<ICustomer>(`customer-create-${Date.now()}`, () =>
      client<{ success: boolean; data: ICustomer }>("/api/admin/customers", {
        method: "POST",
        body: payload,
      }).then((res) => res.data)
    );
  }

  updateCustomer(payload: IUpdateCustomer) {
    const client = useSanctumClient();

    const { customerId, ...data } = payload;

    return useAsyncData<ICustomer>(`customer-update-${customerId}-${Date.now()}`, () =>
      client<{ success: boolean; data: ICustomer }>(
        `/api/admin/customers/${customerId}`,
        {
          method: "PUT",
          body: data,
        }
      ).then((res) => res.data)
    );
  }

  deleteCustomer(id: number) {
    const client = useSanctumClient();

    return useAsyncData<void>(`customer-delete-${id}-${Date.now()}`, () =>
      client<{ success: boolean }>(`/api/admin/customers/${id}`, {
        method: "DELETE",
      }).then(() => undefined)
    );
  }
}
