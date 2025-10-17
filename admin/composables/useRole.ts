import { RoleService } from "~/services/RoleService";
import { type IRole, type IRoleProvider } from "~/models/roles";

let provider: IRoleProvider = new RoleService();

export const useRole = () => {
  return {
    getAllPermissions: () => provider.getAllPermissions(),
    getAllRoles: () => provider.getAllRoles(),
    getRoleById: (id: number) => provider.getRoleById(id),
    createRole: (role: IRole) => provider.createRole(role),
    updateRole: (role: IRole) => provider.updateRole(role),
    deleteRole: (role: IRole) => provider.deleteRole(role),
  };
};
