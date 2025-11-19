import type { IRole } from "./roles";
import type { IPaginationMeta } from "~/composables/usePaginationList";

export interface IAdminProvider {
  getAllAdmins(
    filters?: IAdminFilters
  ): ReturnType<typeof useAsyncData<IAdmin[] | undefined>>;
  getAllAdminsPromise(filters?: IAdminFilters): Promise<IAdminsListResponse>;
  getAdminById(id: number): ReturnType<typeof useAsyncData<IAdmin | undefined>>;
  createAdmin(
    payload: ICreateAdmin
  ): ReturnType<typeof useAsyncData<IAdmin | undefined>>;
  updateAdmin(
    payload: IUpdateAdmin
  ): ReturnType<typeof useAsyncData<IAdmin | undefined>>;
  deleteAdmin(id: number): ReturnType<typeof useAsyncData<void | undefined>>;
}

export interface IAdmin {
  id: number;
  name: string;
  email: string;
  status: "active" | "inactive";
  role?: IRole;
  created_at: string;
  updated_at: string;
}

export interface IAdminFilters {
  search?: string;
  role?: number | null;
  status?: "active" | "inactive" | null;
  page?: number;
  per_page?: number;
}

export interface IAdminsListResponse {
  success: boolean;
  data: IAdmin[];
  meta?: IPaginationMeta;
}

export interface ICreateAdmin {
  name: string;
  email: string;
  role_id: number;
  status?: "active" | "inactive";
  password?: string;
}

export interface IUpdateAdmin {
  adminId: number;
  name: string;
  email: string;
  role_id: number;
  status?: "active" | "inactive";
  password?: string;
}
