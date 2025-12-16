import {
  type IAuthPayload,
  type IAuthProvider,
  type IAuthRegisterPayload,
} from "~/models/auth";
import { useFavoriteStore } from "~/stores/useFavoriteStore";
import { useCartStore } from "~/stores/useCartStore";

export class AuthService implements IAuthProvider {
  async login(payload: IAuthPayload): Promise<void> {
    const { login } = useSanctumAuth();
    await login(payload);

    // Sync favorites and cart after successful login
    const favoriteStore = useFavoriteStore();
    const cartStore = useCartStore();

    await Promise.all([
      favoriteStore.syncWithServer(),
      cartStore.syncWithServer(),
    ]);
  }

  async register(payload: IAuthRegisterPayload): Promise<void> {
    await useSanctumFetch("/api/register", {
      method: "POST",
      body: payload,
    });
  }

  async logout(): Promise<void> {
    const { logout } = useSanctumAuth();
    await logout();

    // Clear favorites and cart stores after logout
    const favoriteStore = useFavoriteStore();
    const cartStore = useCartStore();

    favoriteStore.clear();
    cartStore.clear();
  }
}
