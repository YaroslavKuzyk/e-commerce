import { defineStore } from "pinia";
import { useAdmin } from "~/composables/useAdmin";
import type { IAdminFilters } from "~/models/administrators";

export const useAdminStore = defineStore("admin", () => {
  const {
    getAllAdmins,
    getAllAdminsPromise,
    getAdminById,
    createAdmin,
    updateAdmin,
    deleteAdmin,
  } = useAdmin();

  const fetchAdmins = (filters?: IAdminFilters) => {
    return getAllAdmins(filters);
  };

  const fetchAdminsPromise = async (filters?: IAdminFilters) => {
    return await getAllAdminsPromise(filters);
  };

  const fetchAdminById = (id: number) => {
    return getAdminById(id);
  };

  const onCreateAdmin = async (payload: any) => {
    return await createAdmin(payload);
  };

  const onUpdateAdmin = async (payload: any) => {
    return await updateAdmin(payload);
  };

  const onDeleteAdmin = async (id: number) => {
    return await deleteAdmin(id);
  };

  return {
    fetchAdmins,
    fetchAdminsPromise,
    fetchAdminById,
    onCreateAdmin,
    onUpdateAdmin,
    onDeleteAdmin,
  };
});
