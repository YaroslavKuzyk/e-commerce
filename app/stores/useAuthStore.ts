import { defineStore } from "pinia";
import { type IUser } from "~/models/auth";

export const useAuthStore = defineStore("auth", () => {
  const { user: userData } = useSanctumAuth<{ data: IUser }>();
  const user = computed(() => userData.value?.data);

  return { user };
});
