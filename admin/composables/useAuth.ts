import { AuthService } from "~/services/AuthService";
import {
  type IAuthPayload,
  type IAuthProvider,
  type IAuthRegisterPayload,
  type IUpdateProfilePayload,
  type IUpdatePasswordPayload,
} from "~/models/auth";

let provider: IAuthProvider = new AuthService();

export const useAuth = () => {
  return {
    login: (payload: IAuthPayload) => provider.login(payload),
    register: (payload: IAuthRegisterPayload) => provider.register(payload),
    logout: () => provider.logout(),
    updateProfile: (payload: IUpdateProfilePayload) => provider.updateProfile(payload),
    updatePassword: (payload: IUpdatePasswordPayload) => provider.updatePassword(payload),
    updateAvatar: (avatarFileId: number | null) => provider.updateAvatar(avatarFileId),
  };
};
