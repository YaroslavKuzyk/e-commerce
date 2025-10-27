import { AdminService } from "~/services/AdminService";
import type { IAdminProvider } from "~/models/administrators";

export const useAdmin = (): IAdminProvider => {
  const adminService = new AdminService();

  return {
    getAllAdmins: adminService.getAllAdmins.bind(adminService),
    getAdminById: adminService.getAdminById.bind(adminService),
    createAdmin: adminService.createAdmin.bind(adminService),
    updateAdmin: adminService.updateAdmin.bind(adminService),
    deleteAdmin: adminService.deleteAdmin.bind(adminService),
  };
};
