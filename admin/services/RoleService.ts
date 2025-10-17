import {
  type IPermission,
  type IRole,
  type IRoleProvider,
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

    return useAsyncData<IRole>("role", () =>
      client<{ success: boolean; data: IRole }>(`/api/roles/${id}`).then(
        (res) => res.data
      )
    );
  }

  createRole(role: IRole) {
    const client = useSanctumClient();

    return useAsyncData<IRole>("role", () =>
      client<{ success: boolean; data: IRole }>("/api/roles", {
        method: "POST",
        body: role,
      }).then((res) => res.data)
    );
  }

  updateRole(role: IRole) {
    const client = useSanctumClient();
    return useAsyncData<IRole>("role", () =>
      client<{ success: boolean; data: IRole }>(`/api/roles/${role.id}`, {
        method: "PUT",
        body: role,
      }).then((res) => res.data)
    );
  }

  deleteRole(role: IRole) {
    const client = useSanctumClient();
    return useAsyncData<IRole>("role", () =>
      client<{ success: boolean; data: IRole }>(`/api/roles/${role.id}`, {
        method: "DELETE",
      }).then((res) => res.data)
    );
  }
}
