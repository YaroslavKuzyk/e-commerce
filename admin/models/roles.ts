export interface IRoleProvider {
  getAllPermissions(): ReturnType<typeof useAsyncData<IPermission[] | undefined>>;
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
