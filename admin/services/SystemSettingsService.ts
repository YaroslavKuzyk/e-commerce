import type {
  ISystemSettingsProvider,
  SystemSetting,
  SystemSettingsTypesResponse,
  SystemSettingType,
  UpdateSystemSettingPayload,
} from "~/models/systemSettings";

export class SystemSettingsService implements ISystemSettingsProvider {
  private client = useSanctumClient();

  getAvailableTypes = () => {
    return useAsyncData<SystemSettingsTypesResponse>("system-settings-types", async () => {
      const response = await this.client<{ success: boolean; data: SystemSettingsTypesResponse }>(
        "/api/admin/system-settings"
      );
      return response.data;
    });
  };

  getAvailableTypesPromise = async (): Promise<SystemSettingsTypesResponse> => {
    const response = await this.client<{ success: boolean; data: SystemSettingsTypesResponse }>(
      "/api/admin/system-settings"
    );
    return response.data;
  };

  getSetting = (type: SystemSettingType) => {
    return useAsyncData<SystemSetting>(`system-setting-${type}`, async () => {
      const response = await this.client<{ success: boolean; data: SystemSetting }>(
        `/api/admin/system-settings/${type}`
      );
      return response.data;
    });
  };

  updateSetting = (type: SystemSettingType, payload: UpdateSystemSettingPayload) => {
    return useAsyncData<SystemSetting>(`update-system-setting-${type}-${Date.now()}`, async () => {
      const response = await this.client<{ success: boolean; data: SystemSetting }>(
        `/api/admin/system-settings/${type}`,
        {
          method: "PUT",
          body: payload,
        }
      );
      return response.data;
    });
  };

  updateSettingPromise = async (type: SystemSettingType, payload: UpdateSystemSettingPayload): Promise<SystemSetting> => {
    const response = await this.client<{ success: boolean; data: SystemSetting }>(
      `/api/admin/system-settings/${type}`,
      {
        method: "PUT",
        body: payload,
      }
    );
    return response.data;
  };

  toggleActive = (type: SystemSettingType, isActive: boolean) => {
    return useAsyncData<SystemSetting>(`toggle-system-setting-${type}-${Date.now()}`, async () => {
      const response = await this.client<{ success: boolean; data: SystemSetting }>(
        `/api/admin/system-settings/${type}/toggle-active`,
        {
          method: "PATCH",
          body: { is_active: isActive },
        }
      );
      return response.data;
    });
  };

  toggleActivePromise = async (type: SystemSettingType, isActive: boolean): Promise<SystemSetting> => {
    const response = await this.client<{ success: boolean; data: SystemSetting }>(
      `/api/admin/system-settings/${type}/toggle-active`,
      {
        method: "PATCH",
        body: { is_active: isActive },
      }
    );
    return response.data;
  };

  deleteSetting = (type: SystemSettingType) => {
    return useAsyncData<void>(`delete-system-setting-${type}-${Date.now()}`, async () => {
      await this.client<{ success: boolean }>(
        `/api/admin/system-settings/${type}`,
        {
          method: "DELETE",
        }
      );
    });
  };

  deleteSettingPromise = async (type: SystemSettingType): Promise<void> => {
    await this.client<{ success: boolean }>(
      `/api/admin/system-settings/${type}`,
      {
        method: "DELETE",
      }
    );
  };
}
