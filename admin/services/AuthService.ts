import {
  type IAuthPayload,
  type IAuthProvider,
  type IAuthRegisterPayload,
} from "~/models/auth";

export class AuthService implements IAuthProvider {
  async login(payload: IAuthPayload): Promise<void> {
    const { login } = useSanctumAuth();
    await login(payload);
  }

  async register(payload: IAuthRegisterPayload): Promise<void> {
    await useSanctumFetch("/api/admin/register", {
      method: "POST",
      body: payload,
    });
  }

  async logout(): Promise<void> {
    const { logout } = useSanctumAuth();
    await logout();
  }
}
