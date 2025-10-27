import {
  type IPermission,
  type IRole,
  type IRoleProvider,
  type ICreateRole,
  type IDeleteRole,
  type IUpdateRole,
} from "~/models/roles";

export class RoleService implements IRoleProvider {
  getAllPermissions() {
    const client = useSanctumClient();

    return useAsyncData<IPermission[]>("permissions", () =>
      client<{ success: boolean; data: IPermission[] }>(
        "/api/permissions"
      ).then((res) => res.data)
    );
  }

  getAllRoles() {
    const client = useSanctumClient();

    return useAsyncData<IRole[]>("roles", () =>
      client<{ success: boolean; data: IRole[] }>("/api/roles").then(
        (res) => res.data
      )
    );
  }

  getRoleById(id: number) {
    const client = useSanctumClient();

    return useAsyncData<IRole>(`role-${id}`, () =>
      client<{ success: boolean; data: IRole }>(`/api/roles/${id}`).then(
        (res) => res.data
      )
    );
  }

  createRole(role: ICreateRole) {
    const client = useSanctumClient();

    return useAsyncData<IRole>(`role-create-${Date.now()}`, () =>
      client<{ success: boolean; data: IRole }>("/api/roles", {
        method: "POST",
        body: role,
      }).then((res) => res.data)
    );
  }

  updateRole(payload: IUpdateRole) {
    const client = useSanctumClient();
    return useAsyncData<IRole>(
      `role-update-${payload.roleId}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: IRole }>(
          `/api/roles/${payload.roleId}`,
          {
            method: "PUT",
            body: {
              name: payload.name,
              permissions: payload.permissions,
            },
          }
        ).then((res) => res.data)
    );
  }

  deleteRole(payload: IDeleteRole) {
    const client = useSanctumClient();
    return useAsyncData<IRole>(
      `role-delete-${payload.roleId}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: IRole }>(
          `/api/roles/${payload.roleId}`,
          {
            method: "DELETE",
            body: {
              replacement_role_id: payload.replacementRoleId,
            },
          }
        ).then((res) => res.data)
    );
  }
}
