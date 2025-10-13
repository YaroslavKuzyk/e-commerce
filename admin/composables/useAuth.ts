import { AuthService } from "~/services/AuthService";
import {
  type IAuthPayload,
  type IAuthProvider,
  type IAuthRegisterPayload,
} from "~/models/auth";

let provider: IAuthProvider = new AuthService();

export const useAuth = () => {
  return {
    login: (payload: IAuthPayload) => provider.login(payload),
    register: (payload: IAuthRegisterPayload) => provider.register(payload),
    logout: () => provider.logout(),
  };
};
