import { defineStore } from "pinia";
import type { StoreSettings } from "~/models/storeSettings";

export const useStoreSettingsStore = defineStore("storeSettings", () => {
  const { getStoreSettings, updateStoreSettingsPromise } = useStoreSettings();

  const fetchStoreSettings = () => {
    return getStoreSettings();
  };

  const onUpdateStoreSettings = async (payload: Partial<StoreSettings>) => {
    return await updateStoreSettingsPromise(payload);
  };

  return {
    fetchStoreSettings,
    onUpdateStoreSettings,
  };
});
