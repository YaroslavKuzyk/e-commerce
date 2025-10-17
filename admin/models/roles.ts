export interface IRoleProvider {
  getAllPermissions(): ReturnType<
    typeof useAsyncData<IPermission[] | undefined>
  >;
  getAllRoles(): ReturnType<typeof useAsyncData<IRole[] | undefined>>;
  getRoleById(id: number): ReturnType<typeof useAsyncData<IRole | undefined>>;
  createRole(role: IRole): ReturnType<typeof useAsyncData<IRole | undefined>>;
  updateRole(role: IRole): ReturnType<typeof useAsyncData<IRole | undefined>>;
  deleteRole(role: IRole): ReturnType<typeof useAsyncData<IRole | undefined>>;
}

export interface IPermission {
  id: number;
  name: string;
  type: string;
  group: string;
}

export interface IRole {
  id: number;
  name: string;
  permissions: IPermission[];
}
