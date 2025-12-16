import type { StoreSettings, IStoreSettingsProvider } from "~/models/storeSettings";

export class StoreSettingsService implements IStoreSettingsProvider {
  getStoreSettings() {
    const client = useSanctumClient();

    return useAsyncData<StoreSettings>("store-settings", () =>
      client<{ success: boolean; data: StoreSettings }>(
        `/api/store-settings`
      ).then((res) => res.data)
    );
  }
}
