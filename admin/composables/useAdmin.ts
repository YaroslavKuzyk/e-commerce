import { AdminService } from "~/services/AdminService";
import type {
  IAdminProvider,
  ICreateAdmin,
  IUpdateAdmin,
  IAdminFilters,
} from "~/models/administrators";

let provider: IAdminProvider = new AdminService();

export const useAdmin = () => {
  return {
    getAllAdmins: (filters?: IAdminFilters) => provider.getAllAdmins(filters),
    getAllAdminsPromise: (filters?: IAdminFilters) => provider.getAllAdminsPromise(filters),
    getAdminById: (id: number) => provider.getAdminById(id),
    createAdmin: (payload: ICreateAdmin) => provider.createAdmin(payload),
    updateAdmin: (payload: IUpdateAdmin) => provider.updateAdmin(payload),
    deleteAdmin: (id: number) => provider.deleteAdmin(id),
  };
};
