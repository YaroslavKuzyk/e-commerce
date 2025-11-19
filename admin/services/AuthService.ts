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
    const userData = useSanctumUser<{ data: IUser }>();

    if (!userData.value?.data) {
      throw new Error("User not authenticated");
    }

    const user = userData.value.data;

    const response = await useSanctumClient()<{ success: boolean; data: IUser }>(
      `/api/admin/users/${user.id}`,
      {
        method: "PUT",
        body: {
          name: payload.name,
          email: payload.email,
          role_id: user.role?.id,
          status: user.status,
        },
      }
    );

    // Refresh user data
    const { refreshIdentity } = useSanctumAuth();
    await refreshIdentity();

    return response.data;
  }

  async updatePassword(payload: IUpdatePasswordPayload): Promise<void> {
    const userData = useSanctumUser<{ data: IUser }>();

    if (!userData.value?.data) {
      throw new Error("User not authenticated");
    }

    const user = userData.value.data;

    await useSanctumClient()(
      `/api/admin/users/${user.id}`,
      {
        method: "PUT",
        body: {
          name: user.name,
          email: user.email,
          role_id: user.role?.id,
          status: user.status,
          password: payload.password,
        },
      }
    );
  }

  async updateAvatar(avatarFileId: number | null): Promise<IUser> {
    const userData = useSanctumUser<{ data: IUser }>();

    if (!userData.value?.data) {
      throw new Error("User not authenticated");
    }

    const user = userData.value.data;

    const response = await useSanctumClient()<{ success: boolean; data: IUser }>(
      `/api/admin/users/${user.id}`,
      {
        method: "PATCH",
        body: {
          avatar_file_id: avatarFileId,
        },
      }
    );

    // Refresh user data
    const { refreshIdentity } = useSanctumAuth();
    await refreshIdentity();

    return response.data;
  }
}
