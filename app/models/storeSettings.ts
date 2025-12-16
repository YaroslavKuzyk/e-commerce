export type PhoneDisplayType = 'text' | 'button' | 'button-link' | 'link';

export interface PhoneContact {
  label: string;
  number: string;
  display_type: PhoneDisplayType;
}

export interface EmailContact {
  label: string;
  email: string;
}

export interface WorkingHoursEntry {
  label: string;
  from: string;
  to: string;
}

export interface WorkingHours {
  weekdays: WorkingHoursEntry;
  weekends: WorkingHoursEntry;
}

export interface FooterWorkingHoursPhone {
  label: string;
  value: string;
}

export interface FooterWorkingHours {
  weekdays: WorkingHoursEntry;
  weekends: WorkingHoursEntry;
  phone1: FooterWorkingHoursPhone;
  phone2: FooterWorkingHoursPhone;
}

export interface SocialLink {
  platform: string;
  url: string;
  name?: string;
  followers?: string;
}

export interface GeneralSettings {
  store_name: string;
  favicon_file_id: number | null;
  logo_file_id: number | null;
}

export interface ContactsSettings {
  phones: PhoneContact[];
  emails: EmailContact[];
}

export interface StoreSettings {
  general: GeneralSettings;
  contacts: ContactsSettings;
  working_hours: WorkingHours;
  footer_working_hours: FooterWorkingHours;
  social_links: SocialLink[];
}

export interface IStoreSettingsProvider {
  getStoreSettings: () => ReturnType<typeof useAsyncData<StoreSettings>>;
}
