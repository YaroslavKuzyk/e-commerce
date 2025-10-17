import { defineStore } from "pinia";
import { useRole } from "~/composables/useRole";
import type { IRole } from "~/models/roles";

export const useRoleStore = defineStore("role", () => {
  const {
    getAllPermissions,
    getAllRoles,
    getRoleById,
    createRole,
    updateRole,
    deleteRole,
  } = useRole();

  const fetchPermissions = async () => {
    return await getAllPermissions();
  };

  const fetchRoles = async () => {
    return await getAllRoles();
  };

  const fetchRoleById = async (id: number) => {
    return await getRoleById(id);
  };

  const onCreateRole = async (role: IRole) => {
    return await createRole(role);
  };

  const onUpdateRole = async (role: IRole) => {
    return await updateRole(role);
  };

  const onDeleteRole = async (role: IRole) => {
    return await deleteRole(role);
  };

  return {
    fetchPermissions,
    fetchRoles,
    fetchRoleById,
    onCreateRole,
    onUpdateRole,
    onDeleteRole,
  };
});
