import { defineStore } from "pinia";
import { useAdmin } from "~/composables/useAdmin";
import type { IAdminFilters } from "~/models/administrators";

export const useAdminStore = defineStore("admin", () => {
  const adminComposable = useAdmin();

  const fetchAdmins = (filters?: IAdminFilters) => {
    return adminComposable.getAllAdmins(filters);
  };

  const fetchAdminById = (id: number) => {
    return adminComposable.getAdminById(id);
  };

  const onCreateAdmin = (payload: any) => {
    return adminComposable.createAdmin(payload);
  };

  const onUpdateAdmin = (payload: any) => {
    return adminComposable.updateAdmin(payload);
  };

  const onDeleteAdmin = (id: number) => {
    return adminComposable.deleteAdmin(id);
  };

  return {
    fetchAdmins,
    fetchAdminById,
    onCreateAdmin,
    onUpdateAdmin,
    onDeleteAdmin,
  };
});
