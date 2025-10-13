import { type IPermission, type IRoleProvider } from "~/models/roles";

export class RoleService implements IRoleProvider {
  getAllPermissions() {
    const client = useSanctumClient();

    return useAsyncData<IPermission[]>("permissions", () =>
      client<{ success: boolean; data: IPermission[] }>("/api/permissions").then(
        (res) => res.data
      )
    );
  }
}
