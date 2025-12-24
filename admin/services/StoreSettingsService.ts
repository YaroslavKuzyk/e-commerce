import type { IStoreSettingsProvider, StoreSettings } from "~/models/storeSettings";

export class StoreSettingsService implements IStoreSettingsProvider {
  private client = useSanctumClient();

  getStoreSettings = () => {
    return useAsyncData<StoreSettings>("store-settings", async () => {
      const response = await this.client<{ success: boolean; data: StoreSettings }>(
        "/api/admin/store-settings"
      );
      return response.data;
    });
  };

  updateStoreSettings = (payload: Partial<StoreSettings>) => {
    return useAsyncData<StoreSettings>(`update-store-settings-${Date.now()}`, async () => {
      const response = await this.client<{ success: boolean; data: StoreSettings }>(
        "/api/admin/store-settings",
        {
          method: "PUT",
          body: payload,
        }
      );
      return response.data;
    });
  };

  updateStoreSettingsPromise = async (payload: Partial<StoreSettings>): Promise<StoreSettings> => {
    const response = await this.client<{ success: boolean; data: StoreSettings }>(
      "/api/admin/store-settings",
      {
        method: "PUT",
        body: payload,
      }
    );
    return response.data;
  };
}
