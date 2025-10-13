import { RoleService } from "~/services/RoleService";
import { type IRoleProvider } from "~/models/roles";

let provider: IRoleProvider = new RoleService();

export const useRole = () => {
  return {
    getAllPermissions: () => provider.getAllPermissions(),
  };
};
