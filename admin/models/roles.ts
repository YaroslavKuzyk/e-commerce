export interface IRoleProvider {
  getAllPermissions(): ReturnType<
    typeof useAsyncData<IPermission[] | undefined>
  >;
  getAllRoles(): ReturnType<typeof useAsyncData<IRole[] | undefined>>;
  getRoleById(id: number): ReturnType<typeof useAsyncData<IRole | undefined>>;
  createRole(
    payload: ICreateRole
  ): ReturnType<typeof useAsyncData<IRole | undefined>>;
  updateRole(
    payload: IUpdateRole
  ): ReturnType<typeof useAsyncData<IRole | undefined>>;
  deleteRole(
    payload: IDeleteRole
  ): ReturnType<typeof useAsyncData<IRole | undefined>>;
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

export interface ICreateRole {
  name: string;
  permissions: number[];
}

export interface IDeleteRole {
  roleId: number;
  replacementRoleId: number;
}

export interface IUpdateRole {
  roleId: number;
  name: string;
  permissions: number[];
}
