import { defineStore } from "pinia";
import { useRole } from "~/composables/useRole";
import type { IPermission } from "~/models/roles";

export const useRoleStore = defineStore("role", () => {
  const { getAllPermissions } = useRole();

  const fetchPermissions = async () => {
    return await getAllPermissions();
  };

  return { fetchPermissions };
});
