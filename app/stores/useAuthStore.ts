import { defineStore } from "pinia";
import { type IUser } from "~/models/auth";
import { useAuth } from "~/composables/useAuth";

export const useAuthStore = defineStore("auth", () => {
  const { login, register, logout } = useAuth();
  const { user: userData } = useSanctumAuth<{ data: IUser }>();
  const user = computed(() => userData.value?.data);

  return { user, login, register, logout };
});
