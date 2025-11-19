<template>
  <VSidebarContent title="Налаштування профілю">
    <template #toolbar>
      <div class="flex items-center gap-2 w-full">
        <UButton
          to="/profile"
          variant="ghost"
          color="neutral"
        >
          <template #leading>
            <ArrowLeft class="w-5 h-5" />
          </template>
          Повернутись до профілю
        </UButton>
      </div>
    </template>

    <div class="p-6">
      <div v-if="user" class="grid grid-cols-1 lg:grid-cols-[400px_1fr] gap-6">
        <!-- Left Column - Profile Preview -->
        <div class="space-y-6">
          <!-- Profile Card -->
          <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden"
          >
            <!-- Avatar Section -->
            <div class="relative">
              <div
                class="h-32 bg-gradient-to-br from-primary-400 to-primary-600"
              ></div>
              <div
                class="absolute -bottom-16 left-1/2 transform -translate-x-1/2"
              >
                <VAvatar :name="previewData.name" :file-id="user.avatar_file_id" size="2xl" :border="true" />
              </div>
            </div>

            <!-- Profile Info -->
            <div class="pt-20 pb-6 px-6 text-center">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                {{ previewData.name }}
              </h2>
              <div
                v-if="user.role"
                class="inline-flex items-center justify-center mb-4"
              >
                <UBadge color="neutral" size="md" variant="subtle">
                  {{ user.role.name }}
                </UBadge>
              </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-200 dark:border-gray-700"></div>

            <!-- Details Section -->
            <div class="p-6">
              <h3
                class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4"
              >
                Деталі
              </h3>
              <dl class="space-y-3">
                <div>
                  <dt
                    class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1"
                  >
                    Ім'я:
                  </dt>
                  <dd class="text-sm text-gray-900 dark:text-white">
                    {{ previewData.name }}
                  </dd>
                </div>
                <div>
                  <dt
                    class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1"
                  >
                    Email:
                  </dt>
                  <dd class="text-sm text-gray-900 dark:text-white">
                    {{ previewData.email }}
                  </dd>
                </div>
                <div>
                  <dt
                    class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1"
                  >
                    Роль:
                  </dt>
                  <dd class="text-sm text-gray-900 dark:text-white">
                    {{ user.role?.name || "Не призначена" }}
                  </dd>
                </div>
              </dl>
            </div>
          </div>
        </div>

        <!-- Right Column - Settings Forms -->
        <div class="space-y-6">
          <!-- Profile Information Section -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center gap-3">
                <div
                  class="p-2 bg-primary-100 dark:bg-primary-900/20 rounded-lg w-10 h-10 flex items-center justify-center"
                >
                  <User class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                  Персональна інформація
                </h3>
              </div>
            </div>

            <div class="p-6">
              <UForm
                :schema="profileSchema"
                :state="profileForm"
                @submit="updateProfile"
                class="space-y-4"
              >
                <UFormGroup label="Ім'я" name="name" required>
                  <UInput
                    class="w-full mb-2"
                    v-model="profileForm.name"
                    placeholder="Введіть ваше ім'я"
                  />
                </UFormGroup>

                <UFormGroup label="Email" name="email" required>
                  <UInput
                    class="w-full"
                    v-model="profileForm.email"
                    type="email"
                    placeholder="Введіть ваш email"
                  />
                </UFormGroup>

                <div class="flex justify-end space-x-3 pt-2">
                  <UButton
                    type="button"
                    variant="outline"
                    color="neutral"
                    @click="resetProfileForm"
                    :disabled="isUpdatingProfile"
                  >
                    <template #leading>
                      <Ban class="w-4 h-4" />
                    </template>
                    Скасувати
                  </UButton>
                  <UButton
                    type="submit"
                    color="primary"
                    :loading="isUpdatingProfile"
                  >
                    <template #leading>
                      <Send class="w-4 h-4" />
                    </template>
                    Підтвердити
                  </UButton>
                </div>
              </UForm>
            </div>
          </div>

          <!-- Password Section -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center gap-3">
                <div
                  class="p-2 bg-primary-100 dark:bg-primary-900/20 rounded-lg w-10 h-10 flex items-center justify-center"
                >
                  <Lock class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                  Зміна пароля
                </h3>
              </div>
            </div>

            <div class="p-6">
              <UForm
                :schema="passwordSchema"
                :state="passwordForm"
                @submit="updatePassword"
                class="space-y-4"
              >
                <UFormGroup label="Новий пароль" name="password" required>
                  <UInput
                    class="w-full mb-2"
                    v-model="passwordForm.password"
                    :type="showPassword ? 'text' : 'password'"
                    placeholder="Введіть новий пароль"
                  >
                    <template #trailing>
                      <UButton
                        color="neutral"
                        variant="link"
                        size="xs"
                        @click="showPassword = !showPassword"
                        tabindex="-1"
                      >
                        <Eye v-if="!showPassword" class="w-4 h-4" />
                        <EyeOff v-else class="w-4 h-4" />
                      </UButton>
                    </template>
                  </UInput>
                </UFormGroup>

                <UFormGroup
                  label="Підтвердження пароля"
                  name="password_confirmation"
                  required
                >
                  <UInput
                    class="w-full"
                    v-model="passwordForm.password_confirmation"
                    :type="showPasswordConfirmation ? 'text' : 'password'"
                    placeholder="Підтвердіть новий пароль"
                  >
                    <template #trailing>
                      <UButton
                        color="neutral"
                        variant="link"
                        size="xs"
                        @click="showPasswordConfirmation = !showPasswordConfirmation"
                        tabindex="-1"
                      >
                        <Eye v-if="!showPasswordConfirmation" class="w-4 h-4" />
                        <EyeOff v-else class="w-4 h-4" />
                      </UButton>
                    </template>
                  </UInput>
                </UFormGroup>

                <div class="flex justify-end space-x-3 pt-2">
                  <UButton
                    type="button"
                    variant="outline"
                    color="neutral"
                    @click="resetPasswordForm"
                    :disabled="isUpdatingPassword"
                  >
                    <template #leading>
                      <Ban class="w-4 h-4" />
                    </template>
                    Скасувати
                  </UButton>
                  <UButton
                    type="submit"
                    color="primary"
                    :loading="isUpdatingPassword"
                  >
                    <template #leading>
                      <Send class="w-4 h-4" />
                    </template>
                    Підтвердити
                  </UButton>
                </div>
              </UForm>
            </div>
          </div>

          <!-- Avatar Section -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center gap-3">
                <div
                  class="p-2 bg-primary-100 dark:bg-primary-900/20 rounded-lg w-10 h-10 flex items-center justify-center"
                >
                  <ImageIcon class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                  Аватар
                </h3>
              </div>
            </div>

            <div class="p-6">
              <div class="flex items-center gap-4 mb-4">
                <VAvatar
                  :name="user.name"
                  :file-id="user.avatar_file_id"
                  size="xl"
                />
                <div class="flex-1">
                  <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                    {{ user.avatar_file_id ? 'Завантажте нове зображення або видаліть поточне' : 'Завантажте зображення для вашого аватара' }}
                  </p>
                  <div class="flex gap-2">
                    <UButton
                      color="primary"
                      variant="outline"
                      @click="openAvatarPicker"
                      :loading="isUpdatingAvatar"
                    >
                      <template #leading>
                        <Upload class="w-4 h-4" />
                      </template>
                      Завантажити фото
                    </UButton>
                    <UButton
                      v-if="user.avatar_file_id"
                      color="error"
                      variant="outline"
                      @click="removeAvatar"
                      :loading="isUpdatingAvatar"
                    >
                      <template #leading>
                        <Trash2 class="w-4 h-4" />
                      </template>
                      Видалити
                    </UButton>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center text-gray-500 dark:text-gray-400">
        Не вдалося завантажити дані профілю
      </div>
    </div>

    <!-- Avatar Picker Modal -->
    <VFilePickerModal
      v-model:is-open="isAvatarPickerOpen"
      file-type="image"
      :max-files="1"
      @select="handleAvatarSelect"
    />
  </VSidebarContent>
</template>

<script setup lang="ts">
import { z } from "zod";
import { ArrowLeft, User, Lock, Image as ImageIcon, Upload, X, Eye, EyeOff, Send, Ban, Trash2 } from "lucide-vue-next";
import type { IUser } from "~/models/auth";
import type { IFile } from "~/models/files";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VFilePickerModal from "~/components/files/modals/VFilePickerModal.vue";

definePageMeta({
  middleware: ["sanctum:auth"],
});

const userData = useSanctumUser<{ data: IUser }>();
const user = computed(() => userData.value?.data);

const {
  updateProfile: updateProfileService,
  updatePassword: updatePasswordService,
  updateAvatar: updateAvatarService,
} = useAuth();
const toast = useToast();

// Profile form schema
const profileSchema = z.object({
  name: z.string().min(1, "Ім'я обов'язкове").max(255, "Ім'я занадто довге"),
  email: z.string().email("Некоректний email"),
});

// Password form schema
const passwordSchema = z
  .object({
    password: z
      .string()
      .min(8, "Пароль повинен містити мінімум 8 символів")
      .max(255, "Пароль занадто довгий"),
    password_confirmation: z.string(),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: "Паролі не співпадають",
    path: ["password_confirmation"],
  });

// Profile form
const profileForm = ref({
  name: user.value?.name || "",
  email: user.value?.email || "",
});

const isUpdatingProfile = ref(false);

// Password form
const passwordForm = ref({
  password: "",
  password_confirmation: "",
});

const isUpdatingPassword = ref(false);

// Password visibility
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

// Avatar picker
const isAvatarPickerOpen = ref(false);
const isUpdatingAvatar = ref(false);

const openAvatarPicker = () => {
  isAvatarPickerOpen.value = true;
};

const handleAvatarSelect = async (files: IFile[]) => {
  if (files.length === 0 || !user.value) return;

  isUpdatingAvatar.value = true;

  try {
    await updateAvatarService(files[0].id);

    toast.add({
      title: "Успішно",
      description: "Аватар успішно оновлено",
    });
  } catch (error: any) {
    const errorMessage = error?.data?.message || "Не вдалося оновити аватар";
    toast.add({
      title: "Помилка",
      description: errorMessage,
      color: "error",
    });
  } finally {
    isUpdatingAvatar.value = false;
  }
};

const removeAvatar = async () => {
  if (!user.value) return;

  isUpdatingAvatar.value = true;

  try {
    await updateAvatarService(null);

    toast.add({
      title: "Успішно",
      description: "Аватар успішно видалено",
    });
  } catch (error: any) {
    const errorMessage = error?.data?.message || "Не вдалося видалити аватар";
    toast.add({
      title: "Помилка",
      description: errorMessage,
      color: "error",
    });
  } finally {
    isUpdatingAvatar.value = false;
  }
};

// Watch for user data changes
watch(
  user,
  (newUser) => {
    if (newUser) {
      profileForm.value = {
        name: newUser.name,
        email: newUser.email,
      };
    }
  },
  { immediate: true }
);

// Preview data - combines user data with form changes
const previewData = computed(() => ({
  name: profileForm.value.name || user.value?.name || "",
  email: profileForm.value.email || user.value?.email || "",
}));

const resetProfileForm = () => {
  profileForm.value = {
    name: user.value?.name || "",
    email: user.value?.email || "",
  };
};

const resetPasswordForm = () => {
  passwordForm.value = {
    password: "",
    password_confirmation: "",
  };
  showPassword.value = false;
  showPasswordConfirmation.value = false;
};

const updateProfile = async () => {
  if (!user.value) return;

  isUpdatingProfile.value = true;

  try {
    await updateProfileService({
      name: profileForm.value.name,
      email: profileForm.value.email,
    });

    toast.add({
      title: "Успішно",
      description: "Профіль успішно оновлено",
    });
  } catch (error: any) {
    const errorMessage = error?.data?.message || "Не вдалося оновити профіль";
    toast.add({
      title: "Помилка",
      description: errorMessage,
      color: "error",
    });
  } finally {
    isUpdatingProfile.value = false;
  }
};

const updatePassword = async () => {
  if (!user.value) return;

  isUpdatingPassword.value = true;

  try {
    await updatePasswordService({
      password: passwordForm.value.password,
      password_confirmation: passwordForm.value.password_confirmation,
    });

    toast.add({
      title: "Успішно",
      description: "Пароль успішно змінено",
    });

    // Reset password form
    resetPasswordForm();
  } catch (error: any) {
    const errorMessage = error?.data?.message || "Не вдалося змінити пароль";
    toast.add({
      title: "Помилка",
      description: errorMessage,
      color: "error",
    });
  } finally {
    isUpdatingPassword.value = false;
  }
};
</script>
