import { RoleService } from "~/services/RoleService";
import {
  type ICreateRole,
  type IDeleteRole,
  type IRoleProvider,
  type IUpdateRole,
} from "~/models/roles";

let provider: IRoleProvider = new RoleService();

export const useRole = () => {
  return {
    getAllPermissions: () => provider.getAllPermissions(),
    getAllRoles: () => provider.getAllRoles(),
    getRoleById: (id: number) => provider.getRoleById(id),
    createRole: (payload: ICreateRole) => provider.createRole(payload),
    updateRole: (payload: IUpdateRole) => provider.updateRole(payload),
    deleteRole: (payload: IDeleteRole) => provider.deleteRole(payload),
  };
};
