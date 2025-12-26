export type SystemSettingType =
  | 'nova_poshta'
  | 'ukrposhta'
  | 'meest'
  | 'monopay'
  | 'liqpay'
  | 'sms_club'
  | 'turbosms';

export type SystemSettingCategory = 'delivery' | 'payment' | 'sms';

export interface NovaPoshtaData {
  api_key: string;
}

export interface UkrposhtaData {
  bearer_token: string;
}

export interface MeestData {
  api_key: string;
  api_secret: string;
}

export interface MonopayData {
  token: string;
}

export interface LiqpayData {
  public_key: string;
  private_key: string;
}

export interface SmsClubData {
  token: string;
}

export interface TurbosmsData {
  login: string;
  password: string;
}

export type SystemSettingData =
  | NovaPoshtaData
  | UkrposhtaData
  | MeestData
  | MonopayData
  | LiqpayData
  | SmsClubData
  | TurbosmsData;

export interface SystemSetting {
  id: number;
  type: SystemSettingType;
  name: string;
  name_uk: string | null;
  description: string | null;
  description_uk: string | null;
  data: SystemSettingData | null;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface SystemSettingTypeInfo {
  type: SystemSettingType;
  name: string;
  name_uk: string;
  description: string;
  description_uk: string;
  category: SystemSettingCategory;
  is_configured: boolean;
  is_active: boolean;
  data: Record<string, string> | null;
  default_structure: Record<string, string>;
}

export type SystemSettingsTypesResponse = SystemSettingTypeInfo[];

export interface UpdateSystemSettingPayload {
  data: Record<string, string>;
  is_active?: boolean;
}

export interface ISystemSettingsProvider {
  getAvailableTypes: () => ReturnType<typeof useAsyncData<SystemSettingsTypesResponse>>;
  getSetting: (type: SystemSettingType) => ReturnType<typeof useAsyncData<SystemSetting>>;
  updateSetting: (type: SystemSettingType, payload: UpdateSystemSettingPayload) => ReturnType<typeof useAsyncData<SystemSetting>>;
  toggleActive: (type: SystemSettingType, isActive: boolean) => ReturnType<typeof useAsyncData<SystemSetting>>;
  deleteSetting: (type: SystemSettingType) => ReturnType<typeof useAsyncData<void>>;
}
