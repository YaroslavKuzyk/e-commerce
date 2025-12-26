import { SystemSettingsService } from "~/services/SystemSettingsService";
import type { SystemSettingType, UpdateSystemSettingPayload } from "~/models/systemSettings";

export const useSystemSettings = () => {
  const service = new SystemSettingsService();

  const getAvailableTypes = () => {
    return service.getAvailableTypes();
  };

  const getAvailableTypesPromise = () => {
    return service.getAvailableTypesPromise();
  };

  const getSetting = (type: SystemSettingType) => {
    return service.getSetting(type);
  };

  const updateSetting = (type: SystemSettingType, payload: UpdateSystemSettingPayload) => {
    return service.updateSetting(type, payload);
  };

  const updateSettingPromise = (type: SystemSettingType, payload: UpdateSystemSettingPayload) => {
    return service.updateSettingPromise(type, payload);
  };

  const toggleActive = (type: SystemSettingType, isActive: boolean) => {
    return service.toggleActive(type, isActive);
  };

  const toggleActivePromise = (type: SystemSettingType, isActive: boolean) => {
    return service.toggleActivePromise(type, isActive);
  };

  const deleteSetting = (type: SystemSettingType) => {
    return service.deleteSetting(type);
  };

  const deleteSettingPromise = (type: SystemSettingType) => {
    return service.deleteSettingPromise(type);
  };

  return {
    getAvailableTypes,
    getAvailableTypesPromise,
    getSetting,
    updateSetting,
    updateSettingPromise,
    toggleActive,
    toggleActivePromise,
    deleteSetting,
    deleteSettingPromise,
  };
};
