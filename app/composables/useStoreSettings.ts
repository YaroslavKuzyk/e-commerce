import type { StoreSettings } from "~/models/storeSettings";

export const useStoreSettings = () => {
  const client = useSanctumClient();

  const { data: storeSettings } = useAsyncData<StoreSettings>(
    'store-settings',
    () => client<{ success: boolean; data: StoreSettings }>('/api/store-settings').then((res) => res.data),
    {
      default: () => ({
        general: { store_name: '', favicon_file_id: null, logo_file_id: null },
        contacts: { phones: [], emails: [] },
        working_hours: {
          weekdays: { label: 'Пн-Пт', from: '09:00', to: '20:00' },
          weekends: { label: 'Сб-Нд', from: '10:00', to: '20:00' },
        },
        footer_working_hours: {
          weekdays: { label: 'Пн-Пт', from: '09:00', to: '20:00' },
          weekends: { label: 'Сб-Нд', from: '10:00', to: '20:00' },
          phone1: { label: '', value: '' },
          phone2: { label: '', value: '' },
        },
        social_links: [],
      }),
    }
  );

  // Computed helpers for easy access
  const storeName = computed(() => storeSettings.value?.general?.store_name || '');
  const logoFileId = computed(() => storeSettings.value?.general?.logo_file_id || null);
  const faviconFileId = computed(() => storeSettings.value?.general?.favicon_file_id || null);

  const phones = computed(() => storeSettings.value?.contacts?.phones || []);
  const emails = computed(() => storeSettings.value?.contacts?.emails || []);

  const workingHours = computed(() => storeSettings.value?.working_hours || {
    weekdays: { label: 'Пн-Пт', from: '09:00', to: '20:00' },
    weekends: { label: 'Сб-Нд', from: '10:00', to: '20:00' },
  });

  const socialLinks = computed(() => storeSettings.value?.social_links || []);

  const footerWorkingHours = computed(() => storeSettings.value?.footer_working_hours || {
    weekdays: { label: 'Пн-Пт', from: '09:00', to: '20:00' },
    weekends: { label: 'Сб-Нд', from: '10:00', to: '20:00' },
    phone1: { label: '', value: '' },
    phone2: { label: '', value: '' },
  });

  return {
    storeSettings,
    storeName,
    logoFileId,
    faviconFileId,
    phones,
    emails,
    workingHours,
    footerWorkingHours,
    socialLinks,
  };
};
