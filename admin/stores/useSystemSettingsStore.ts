import { defineStore } from "pinia";
import type {
  SystemSettingsTypesResponse,
  SystemSettingType,
  SystemSettingTypeInfo,
  UpdateSystemSettingPayload,
} from "~/models/systemSettings";

export const useSystemSettingsStore = defineStore("systemSettings", () => {
  const {
    getAvailableTypesPromise,
    updateSettingPromise,
    toggleActivePromise,
    deleteSettingPromise,
  } = useSystemSettings();

  const types = ref<SystemSettingsTypesResponse | null>(null);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  const fetchTypes = async () => {
    try {
      const data = await getAvailableTypesPromise();
      types.value = data;
      return data;
    } catch (e) {
      console.error('Failed to fetch system settings types:', e);
      return null;
    }
  };

  const onUpdateSetting = async (type: SystemSettingType, payload: UpdateSystemSettingPayload) => {
    isLoading.value = true;
    error.value = null;
    try {
      const result = await updateSettingPromise(type, payload);
      await fetchTypes();
      return result;
    } catch (e: any) {
      error.value = e.message || 'Failed to update setting';
      throw e;
    } finally {
      isLoading.value = false;
    }
  };

  const onToggleActive = async (type: SystemSettingType, isActive: boolean) => {
    isLoading.value = true;
    error.value = null;
    try {
      const result = await toggleActivePromise(type, isActive);
      await fetchTypes();
      return result;
    } catch (e: any) {
      error.value = e.message || 'Failed to toggle setting';
      throw e;
    } finally {
      isLoading.value = false;
    }
  };

  const onDeleteSetting = async (type: SystemSettingType) => {
    isLoading.value = true;
    error.value = null;
    try {
      await deleteSettingPromise(type);
      await fetchTypes();
    } catch (e: any) {
      error.value = e.message || 'Failed to delete setting';
      throw e;
    } finally {
      isLoading.value = false;
    }
  };

  // Group types by category
  const typesByCategory = computed(() => {
    if (!types.value || !Array.isArray(types.value)) return { delivery: [], payment: [], sms: [] };

    const grouped: Record<string, SystemSettingTypeInfo[]> = {
      delivery: [],
      payment: [],
      sms: [],
    };

    for (const item of types.value) {
      if (grouped[item.category]) {
        grouped[item.category].push(item);
      }
    }

    return grouped;
  });

  return {
    types,
    isLoading,
    error,
    typesByCategory,
    fetchTypes,
    onUpdateSetting,
    onToggleActive,
    onDeleteSetting,
  };
});
