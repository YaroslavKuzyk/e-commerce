<template>
  <VSidebarContent :title="$t('storeSettings.title')">
    <HasPermissions :required-permissions="['Read Store Settings']">
      <div v-if="pending" class="flex items-center justify-center py-12">
        <Loader2 class="w-8 h-8 animate-spin text-gray-400" />
      </div>

      <div v-else class="space-y-6">
        <!-- General Settings -->
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <Store class="w-5 h-5" />
            {{ $t('storeSettings.general.title') }}
          </h3>

          <div class="space-y-4">
            <UFormField :label="$t('storeSettings.general.storeName')" name="store_name">
              <UInput
                v-model="form.general.store_name"
                :placeholder="$t('storeSettings.general.storeNamePlaceholder')"
                class="w-full max-w-md"
              />
            </UFormField>

            <div class="grid grid-cols-2 gap-6">
              <UFormField :label="$t('storeSettings.general.favicon')" name="favicon">
                <div class="flex items-center gap-4">
                  <div
                    v-if="form.general.favicon_file_id"
                    class="w-12 h-12 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden"
                  >
                    <VSecureImage
                      :file-id="form.general.favicon_file_id"
                      alt="Favicon"
                      width="w-12"
                      height="h-12"
                      object-fit="contain"
                    />
                  </div>
                  <div v-else class="w-12 h-12 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center">
                    <Image class="w-5 h-5 text-gray-400" />
                  </div>
                  <div class="flex gap-2">
                    <UButton
                      type="button"
                      variant="outline"
                      size="sm"
                      @click="openFilePicker('favicon')"
                    >
                      {{ $t('common.select') }}
                    </UButton>
                    <UButton
                      v-if="form.general.favicon_file_id"
                      type="button"
                      variant="ghost"
                      color="error"
                      size="sm"
                      @click="form.general.favicon_file_id = null"
                    >
                      <X class="w-4 h-4" />
                    </UButton>
                  </div>
                </div>
              </UFormField>

              <UFormField :label="$t('storeSettings.general.logo')" name="logo">
                <div class="flex items-center gap-4">
                  <div
                    v-if="form.general.logo_file_id"
                    class="w-24 h-12 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden"
                  >
                    <VSecureImage
                      :file-id="form.general.logo_file_id"
                      alt="Logo"
                      width="w-24"
                      height="h-12"
                      object-fit="contain"
                    />
                  </div>
                  <div v-else class="w-24 h-12 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center">
                    <Image class="w-5 h-5 text-gray-400" />
                  </div>
                  <div class="flex gap-2">
                    <UButton
                      type="button"
                      variant="outline"
                      size="sm"
                      @click="openFilePicker('logo')"
                    >
                      {{ $t('common.select') }}
                    </UButton>
                    <UButton
                      v-if="form.general.logo_file_id"
                      type="button"
                      variant="ghost"
                      color="error"
                      size="sm"
                      @click="form.general.logo_file_id = null"
                    >
                      <X class="w-4 h-4" />
                    </UButton>
                  </div>
                </div>
              </UFormField>
            </div>
          </div>
        </div>

        <!-- Contact Phones -->
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold flex items-center gap-2">
              <Phone class="w-5 h-5" />
              {{ $t('storeSettings.contacts.phones') }}
            </h3>
            <UButton
              type="button"
              variant="outline"
              size="sm"
              @click="addPhone"
            >
              <Plus class="w-4 h-4 mr-1" />
              {{ $t('common.add') }}
            </UButton>
          </div>

          <div v-if="form.contacts.phones.length === 0" class="text-gray-500 text-sm py-4 text-center">
            {{ $t('storeSettings.contacts.noPhonesAdded') }}
          </div>

          <draggable
            v-else
            v-model="form.contacts.phones"
            item-key="number"
            handle=".drag-handle"
            class="space-y-3"
          >
            <template #item="{ element: phone, index }">
              <div class="flex items-center gap-3">
                <div class="drag-handle cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600">
                  <GripVertical class="w-5 h-5" />
                </div>
                <UInput
                  v-model="phone.label"
                  :placeholder="$t('storeSettings.contacts.labelPlaceholder')"
                  class="w-32"
                />
                <UInput
                  v-model="phone.number"
                  :placeholder="$t('storeSettings.contacts.phonePlaceholder')"
                  class="flex-1"
                />
                <USelectMenu
                  v-model="phone.display_type"
                  :items="displayTypeOptions"
                  value-key="value"
                  label-key="label"
                  class="w-40"
                />
                <UButton
                  type="button"
                  variant="ghost"
                  color="error"
                  size="sm"
                  @click="removePhone(index)"
                >
                  <Trash2 class="w-4 h-4" />
                </UButton>
              </div>
            </template>
          </draggable>
        </div>

        <!-- Contact Emails -->
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold flex items-center gap-2">
              <Mail class="w-5 h-5" />
              {{ $t('storeSettings.contacts.emails') }}
            </h3>
            <UButton
              type="button"
              variant="outline"
              size="sm"
              @click="addEmail"
            >
              <Plus class="w-4 h-4 mr-1" />
              {{ $t('common.add') }}
            </UButton>
          </div>

          <div v-if="form.contacts.emails.length === 0" class="text-gray-500 text-sm py-4 text-center">
            {{ $t('storeSettings.contacts.noEmailsAdded') }}
          </div>

          <draggable
            v-else
            v-model="form.contacts.emails"
            item-key="email"
            handle=".drag-handle"
            class="space-y-3"
          >
            <template #item="{ element: email, index }">
              <div class="flex items-center gap-3">
                <div class="drag-handle cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600">
                  <GripVertical class="w-5 h-5" />
                </div>
                <UInput
                  v-model="email.label"
                  :placeholder="$t('storeSettings.contacts.labelPlaceholder')"
                  class="w-48"
                />
                <UInput
                  v-model="email.email"
                  :placeholder="$t('storeSettings.contacts.emailPlaceholder')"
                  class="flex-1"
                />
                <UButton
                  type="button"
                  variant="ghost"
                  color="error"
                  size="sm"
                  @click="removeEmail(index)"
                >
                  <Trash2 class="w-4 h-4" />
                </UButton>
              </div>
            </template>
          </draggable>
        </div>

        <!-- Working Hours (Footer) -->
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <Clock class="w-5 h-5" />
            {{ $t('storeSettings.workingHours.title') }}
          </h3>

          <div class="space-y-4">
            <div class="flex items-center gap-4">
              <UInput
                v-model="form.footer_working_hours.weekdays.label"
                class="w-24"
              />
              <UInput
                v-model="form.footer_working_hours.weekdays.from"
                type="time"
                class="w-32"
              />
              <span class="text-gray-500">—</span>
              <UInput
                v-model="form.footer_working_hours.weekdays.to"
                type="time"
                class="w-32"
              />
              <UInput
                v-model="form.footer_working_hours.phone1.value"
                :placeholder="$t('storeSettings.footerWorkingHours.phoneValuePlaceholder')"
                class="flex-1"
              />
            </div>

            <div class="flex items-center gap-4">
              <UInput
                v-model="form.footer_working_hours.weekends.label"
                class="w-24"
              />
              <UInput
                v-model="form.footer_working_hours.weekends.from"
                type="time"
                class="w-32"
              />
              <span class="text-gray-500">—</span>
              <UInput
                v-model="form.footer_working_hours.weekends.to"
                type="time"
                class="w-32"
              />
              <UInput
                v-model="form.footer_working_hours.phone2.value"
                :placeholder="$t('storeSettings.footerWorkingHours.phoneValuePlaceholder')"
                class="flex-1"
              />
            </div>
          </div>
        </div>

        <!-- Social Links -->
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold flex items-center gap-2">
              <Share2 class="w-5 h-5" />
              {{ $t('storeSettings.socialLinks.title') }}
            </h3>
            <UButton
              type="button"
              variant="outline"
              size="sm"
              @click="addSocialLink"
            >
              <Plus class="w-4 h-4 mr-1" />
              {{ $t('common.add') }}
            </UButton>
          </div>

          <div v-if="form.social_links.length === 0" class="text-gray-500 text-sm py-4 text-center">
            {{ $t('storeSettings.socialLinks.noLinksAdded') }}
          </div>

          <div v-else class="space-y-3">
            <div
              v-for="(link, index) in form.social_links"
              :key="index"
              class="flex items-center gap-3"
            >
              <USelectMenu
                v-model="link.platform"
                :items="socialPlatforms"
                value-key="value"
                label-key="label"
                class="w-40"
              >
                <template #leading>
                  <component
                    :is="getSocialIcon(link.platform)"
                    class="w-4 h-4"
                  />
                </template>
              </USelectMenu>
              <UInput
                v-model="link.url"
                :placeholder="$t('storeSettings.socialLinks.urlPlaceholder')"
                class="flex-1"
              />
              <UInput
                v-model="link.followers"
                :placeholder="$t('storeSettings.socialLinks.followersPlaceholder')"
                class="w-32"
              />
              <UButton
                type="button"
                variant="ghost"
                color="error"
                size="sm"
                @click="removeSocialLink(index)"
              >
                <Trash2 class="w-4 h-4" />
              </UButton>
            </div>
          </div>
        </div>

        <!-- Slides -->
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold flex items-center gap-2">
              <ImagePlay class="w-5 h-5" />
              {{ $t('storeSettings.slides.title') }}
            </h3>
            <UButton
              type="button"
              variant="outline"
              size="sm"
              @click="openSlideFilePicker"
            >
              <Plus class="w-4 h-4 mr-1" />
              {{ $t('common.add') }}
            </UButton>
          </div>

          <div v-if="form.slides.length === 0" class="text-gray-500 text-sm py-4 text-center">
            {{ $t('storeSettings.slides.noSlidesAdded') }}
          </div>

          <draggable
            v-else
            v-model="form.slides"
            item-key="file_id"
            handle=".drag-handle"
            class="space-y-3"
          >
            <template #item="{ element: slide, index }">
              <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="drag-handle cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600">
                  <GripVertical class="w-5 h-5" />
                </div>
                <div class="w-32 h-20 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden shrink-0">
                  <VSecureImage
                    :file-id="slide.file_id"
                    alt="Slide"
                    width="w-32"
                    height="h-20"
                    object-fit="cover"
                  />
                </div>
                <div class="flex-1">
                  <UFormField :label="$t('storeSettings.slides.link')" class="w-full">
                    <UInput
                      v-model="slide.link"
                      :placeholder="$t('storeSettings.slides.linkPlaceholder')"
                      class="w-full"
                    />
                  </UFormField>
                </div>
                <UButton
                  type="button"
                  variant="ghost"
                  color="error"
                  size="sm"
                  @click="removeSlide(index)"
                >
                  <Trash2 class="w-4 h-4" />
                </UButton>
              </div>
            </template>
          </draggable>
        </div>

        <!-- Save Button -->
        <HasPermissions :required-permissions="['Update Store Settings']">
          <div class="flex justify-end pt-4">
            <UButton
              type="button"
              size="lg"
              :loading="saving"
              @click="saveSettings"
            >
              <Save class="w-4 h-4 mr-2" />
              {{ $t('common.save') }}
            </UButton>
          </div>
        </HasPermissions>
      </div>
    </HasPermissions>

    <!-- File Picker Modal -->
    <VFilePickerModal
      v-model:is-open="isFilePickerOpen"
      :max-files="1"
      file-type="image"
      @select="handleFileSelect"
    />

    <!-- Slide Picker Modal -->
    <VFilePickerModal
      v-model:is-open="isSlidePickerOpen"
      :max-files="10"
      file-type="image"
      @select="handleSlideFileSelect"
    />
  </VSidebarContent>
</template>

<script setup lang="ts">
import {
  Store,
  Phone,
  Mail,
  Clock,
  Share2,
  Plus,
  Trash2,
  Save,
  X,
  Image,
  Loader2,
  Instagram,
  Send,
  Music2,
  Twitter,
  MessageCircle,
  Facebook,
  Youtube,
  Linkedin,
  GripVertical,
  ImagePlay,
} from "lucide-vue-next";
import draggable from "vuedraggable";
import HasPermissions from "~/components/common/VHasPermissions.vue";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VSecureImage from "~/components/common/VSecureImage.vue";
import VFilePickerModal from "~/components/files/modals/VFilePickerModal.vue";
import type { StoreSettings, PhoneContact, EmailContact, SocialLink, Slide } from "~/models/storeSettings";
import type { IFile } from "~/models/files";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Read Store Settings"],
});

const { t } = useI18n();
const toast = useToast();
const storeSettingsStore = useStoreSettingsStore();

const saving = ref(false);
const isFilePickerOpen = ref(false);
const filePickerTarget = ref<'favicon' | 'logo' | 'slide'>('favicon');
const isSlidePickerOpen = ref(false);

const socialPlatforms = [
  { value: 'instagram', label: 'Instagram' },
  { value: 'telegram', label: 'Telegram' },
  { value: 'tiktok', label: 'TikTok' },
  { value: 'twitter', label: 'X (Twitter)' },
  { value: 'discord', label: 'Discord' },
  { value: 'facebook', label: 'Facebook' },
  { value: 'youtube', label: 'YouTube' },
  { value: 'linkedin', label: 'LinkedIn' },
];

const displayTypeOptions = computed(() => [
  { value: 'text', label: t('storeSettings.contacts.displayTypes.text') },
  { value: 'button', label: t('storeSettings.contacts.displayTypes.button') },
  { value: 'button-link', label: t('storeSettings.contacts.displayTypes.buttonLink') },
  { value: 'link', label: t('storeSettings.contacts.displayTypes.link') },
]);

const getSocialIcon = (platform: string) => {
  const icons: Record<string, any> = {
    instagram: Instagram,
    telegram: Send,
    tiktok: Music2,
    twitter: Twitter,
    discord: MessageCircle,
    facebook: Facebook,
    youtube: Youtube,
    linkedin: Linkedin,
  };
  return icons[platform] || Share2;
};

// Fetch settings
const { data: settingsData, pending } = await storeSettingsStore.fetchStoreSettings();

// Form state
const form = reactive<StoreSettings>({
  general: {
    store_name: '',
    favicon_file_id: null,
    logo_file_id: null,
  },
  contacts: {
    phones: [],
    emails: [],
  },
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
  slides: [],
});

// Initialize form with data
watch(settingsData, (data) => {
  if (data) {
    form.general = { ...form.general, ...data.general };
    form.contacts = {
      phones: data.contacts?.phones || [],
      emails: data.contacts?.emails || [],
    };
    form.working_hours = { ...form.working_hours, ...data.working_hours };
    form.footer_working_hours = { ...form.footer_working_hours, ...data.footer_working_hours };
    form.social_links = data.social_links || [];
    form.slides = data.slides || [];
  }
}, { immediate: true });

// Phone management
const addPhone = () => {
  form.contacts.phones.push({ label: '', number: '', display_type: 'text' });
};

const removePhone = (index: number) => {
  form.contacts.phones.splice(index, 1);
};

// Email management
const addEmail = () => {
  form.contacts.emails.push({ label: '', email: '' });
};

const removeEmail = (index: number) => {
  form.contacts.emails.splice(index, 1);
};

// Social link management
const addSocialLink = () => {
  form.social_links.push({ platform: 'instagram', url: '', followers: '' });
};

const removeSocialLink = (index: number) => {
  form.social_links.splice(index, 1);
};

// Slide management
const openSlideFilePicker = () => {
  isSlidePickerOpen.value = true;
};

const handleSlideFileSelect = (files: IFile[]) => {
  files.forEach(file => {
    form.slides.push({ file_id: file.id, link: '' });
  });
  isSlidePickerOpen.value = false;
};

const removeSlide = (index: number) => {
  form.slides.splice(index, 1);
};

// File picker
const openFilePicker = (target: 'favicon' | 'logo') => {
  filePickerTarget.value = target;
  isFilePickerOpen.value = true;
};

const handleFileSelect = (files: IFile[]) => {
  if (files.length > 0) {
    const file = files[0];
    if (filePickerTarget.value === 'favicon') {
      form.general.favicon_file_id = file.id;
    } else {
      form.general.logo_file_id = file.id;
    }
  }
  isFilePickerOpen.value = false;
};

// Save settings
const saveSettings = async () => {
  saving.value = true;

  try {
    // Filter empty phones and emails
    const payload = {
      general: form.general,
      contacts: {
        phones: form.contacts.phones.filter(p => p.label && p.number),
        emails: form.contacts.emails.filter(e => e.label && e.email),
      },
      working_hours: form.working_hours,
      footer_working_hours: form.footer_working_hours,
      social_links: form.social_links.filter(l => l.platform && l.url),
      slides: form.slides.filter(s => s.file_id),
    };

    await storeSettingsStore.onUpdateStoreSettings(payload);

    toast.add({
      title: t('common.success'),
      description: t('storeSettings.saveSuccess'),
      color: 'success',
    });
  } catch (error) {
    toast.add({
      title: t('common.error'),
      description: t('storeSettings.saveError'),
      color: 'error',
    });
  } finally {
    saving.value = false;
  }
};
</script>
