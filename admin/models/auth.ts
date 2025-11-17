import { type IRole } from "./roles";

export interface IAuthProvider {
  login(payload: IAuthPayload): Promise<void>;
  register(payload: IAuthRegisterPayload): Promise<void>;
  logout(): Promise<void>;
  updateProfile(payload: IUpdateProfilePayload): Promise<IUser>;
  updatePassword(payload: IUpdatePasswordPayload): Promise<void>;
}

export interface IAuthPayload {
  email: string;
  password: string;
}

export interface IAuthRegisterPayload {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface IUpdateProfilePayload {
  name: string;
  email: string;
}

export interface IUpdatePasswordPayload {
  password: string;
  password_confirmation: string;
}

export interface IUser {
  id: number;
  name: string;
  email: string;
  status: "active" | "inactive";
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
  role?: IRole;
}
