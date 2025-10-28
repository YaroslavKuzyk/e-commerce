import type {
  IAdmin,
  IAdminProvider,
  ICreateAdmin,
  IUpdateAdmin,
  IAdminFilters,
} from "~/models/administrators";

export class AdminService implements IAdminProvider {
  getAllAdmins(filters?: IAdminFilters) {
    const client = useSanctumClient();

    const queryParams = new URLSearchParams();
    if (filters?.search) queryParams.append("search", filters.search);
    if (filters?.role) queryParams.append("role", filters.role.toString());
    if (filters?.status) queryParams.append("status", filters.status);

    const queryString = queryParams.toString();
    const url = queryString
      ? `/api/admin/users?${queryString}`
      : "/api/admin/users";

    return useAsyncData<IAdmin[]>(
      `admins-${queryString}`,
      () =>
        client<{ success: boolean; data: IAdmin[] }>(url).then(
          (res) => res.data
        )
    );
  }

  getAdminById(id: number) {
    const client = useSanctumClient();

    return useAsyncData<IAdmin>(`admin-${id}`, () =>
      client<{ success: boolean; data: IAdmin }>(
        `/api/admin/users/${id}`
      ).then((res) => res.data)
    );
  }

  createAdmin(payload: ICreateAdmin) {
    const client = useSanctumClient();

    return useAsyncData<IAdmin>(`admin-create-${Date.now()}`, () =>
      client<{ success: boolean; data: IAdmin }>("/api/admin/users", {
        method: "POST",
        body: payload,
      }).then((res) => res.data)
    );
  }

  updateAdmin(payload: IUpdateAdmin) {
    const client = useSanctumClient();

    const { adminId, ...data } = payload;

    return useAsyncData<IAdmin>(`admin-update-${adminId}-${Date.now()}`, () =>
      client<{ success: boolean; data: IAdmin }>(
        `/api/admin/users/${adminId}`,
        {
          method: "PUT",
          body: data,
        }
      ).then((res) => res.data)
    );
  }

  deleteAdmin(id: number) {
    const client = useSanctumClient();

    return useAsyncData<void>(`admin-delete-${id}-${Date.now()}`, () =>
      client<{ success: boolean }>(`/api/admin/users/${id}`, {
        method: "DELETE",
      }).then(() => undefined)
    );
  }
}
