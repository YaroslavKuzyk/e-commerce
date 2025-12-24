import { StoreSettingsService } from "~/services/StoreSettingsService";

export const useStoreSettings = () => {
  const provider = new StoreSettingsService();

  return {
    getStoreSettings: provider.getStoreSettings,
    updateStoreSettings: provider.updateStoreSettings,
    updateStoreSettingsPromise: provider.updateStoreSettingsPromise,
  };
};
