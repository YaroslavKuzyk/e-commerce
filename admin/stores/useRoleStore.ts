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

  const fetchPermissions = async () => {
    return await getAllPermissions();
  };

  const fetchRoles = async () => {
    return await getAllRoles();
  };

  const fetchRoleById = async (id: number) => {
    return await getRoleById(id);
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
