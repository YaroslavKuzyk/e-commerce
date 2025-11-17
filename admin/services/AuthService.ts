import {
  type IAuthPayload,
  type IAuthProvider,
  type IAuthRegisterPayload,
  type IUpdateProfilePayload,
  type IUpdatePasswordPayload,
  type IUser,
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

  async updateProfile(payload: IUpdateProfilePayload): Promise<IUser> {
    const user = useSanctumUser<IUser>();

    if (!user.value) {
      throw new Error("User not authenticated");
    }

    const response = await useSanctumClient()<{ success: boolean; data: IUser }>(
      `/api/admin/users/${user.value.id}`,
      {
        method: "PUT",
        body: {
          name: payload.name,
          email: payload.email,
          role_id: user.value.role?.id,
          status: user.value.status,
        },
      }
    );

    // Refresh user data
    const { refreshIdentity } = useSanctumAuth();
    await refreshIdentity();

    return response.data;
  }

  async updatePassword(payload: IUpdatePasswordPayload): Promise<void> {
    const user = useSanctumUser<IUser>();

    if (!user.value) {
      throw new Error("User not authenticated");
    }

    await useSanctumClient()(
      `/api/admin/users/${user.value.id}`,
      {
        method: "PUT",
        body: {
          name: user.value.name,
          email: user.value.email,
          role_id: user.value.role?.id,
          status: user.value.status,
          password: payload.password,
        },
      }
    );
  }
}
