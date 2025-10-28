import { defineStore } from "pinia";
import { useRole } from "~/composables/useRole";
import type {
  ICreateRole,
  IDeleteRole,
  IRole,
  IUpdateRole,
} from "~/models/roles";

export const useRoleStore = defineStore("role", () => {
  const {
    getAllPermissions,
    getAllRoles,
    getRoleById,
    createRole,
    updateRole,
    deleteRole,
  } = useRole();

  const fetchPermissions = () => {
    return getAllPermissions();
  };

  const fetchRoles = () => {
    return getAllRoles();
  };

  const fetchRoleById = (id: number) => {
    return getRoleById(id);
  };

  const onCreateRole = async (payload: ICreateRole) => {
    return await createRole(payload);
  };

  const onUpdateRole = async (payload: IUpdateRole) => {
    return await updateRole(payload);
  };

  const onDeleteRole = async (payload: IDeleteRole) => {
    return await deleteRole(payload);
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
