<template>
  <UModal
    v-model:open="isOpen"
    :title="item?.name_uk || item?.name || 'Налаштування'"
    :description="item?.description_uk || item?.description"
  >
    <template #body>
      <form v-if="item" @submit.prevent="handleSubmit" class="space-y-4">
        <UFormField
          v-for="field in allFields"
          :key="field"
          :label="formatFieldLabel(field)"
        >
          <UInput
            v-model="formData[field]"
            :type="isCredentialField(field) && !showPasswords[field] ? 'password' : 'text'"
            :placeholder="getFieldPlaceholder(field)"
            class="w-full"
          >
            <template v-if="isCredentialField(field)" #trailing>
              <UButton
                :icon="showPasswords[field] ? 'i-heroicons-eye-slash' : 'i-heroicons-eye'"
                color="neutral"
                variant="ghost"
                size="xs"
                :padded="false"
                @click="showPasswords[field] = !showPasswords[field]"
              />
            </template>
          </UInput>
        </UFormField>

        <USeparator />

        <div class="flex justify-end gap-2">
          <UButton
            type="button"
            variant="outline"
            color="neutral"
            @click="isOpen = false"
          >
            <template #leading>
              <Ban class="w-4 h-4" />
            </template>
            Скасувати
          </UButton>
          <UButton type="submit" :loading="isSaving">
            <template #leading>
              <Send class="w-4 h-4" />
            </template>
            Підтвердити
          </UButton>
        </div>
      </form>
    </template>
  </UModal>
</template>

<script setup lang="ts">
import type { SystemSettingType, SystemSettingTypeInfo } from "~/models/systemSettings";
import { Send, Ban } from "lucide-vue-next";

interface Props {
  item: SystemSettingTypeInfo | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  save: [type: SystemSettingType, data: Record<string, string>];
}>();

const isOpen = defineModel<boolean>({ default: false });
const isSaving = ref(false);
const formData = ref<Record<string, string>>({});
const showPasswords = ref<Record<string, boolean>>({});

// Field labels in Ukrainian
const fieldLabels: Record<string, string> = {
  api_key: "API ключ",
  api_secret: "API секрет",
  token: "Токен",
  bearer_token: "Bearer токен",
  public_key: "Публічний ключ",
  private_key: "Приватний ключ",
  sender_phone: "Телефон відправника",
  sender_name: "Ім'я відправника",
  sender_city_ref: "Ref міста",
  sender_warehouse_ref: "Ref відділення",
  sender_contact_ref: "Ref контактної особи",
  sender_address_ref: "Ref адреси",
  counterparty_uuid: "UUID контрагента",
  webhook_url: "URL вебхука",
  login: "Логін",
  password: "Пароль",
};

// Credential fields (sensitive)
const credentialFieldsList = ["api_key", "api_secret", "token", "bearer_token", "public_key", "private_key", "login", "password"];

// All fields from default structure only
const allFields = computed(() => {
  if (!props.item?.default_structure) return [];
  return Object.keys(props.item.default_structure);
});

// Check if field is credential (sensitive)
const isCredentialField = (field: string): boolean => {
  return credentialFieldsList.includes(field);
};

// Initialize form data when modal opens or item changes
watch([isOpen, () => props.item], ([open, item]) => {
  if (open && item) {
    const defaultStructure = item.default_structure || {};
    const existingData = item.data || {};

    // Only include fields from default_structure
    const newFormData: Record<string, string> = {};
    for (const key of Object.keys(defaultStructure)) {
      newFormData[key] = existingData[key] ?? defaultStructure[key] ?? '';
    }
    formData.value = newFormData;

    // Reset password visibility
    showPasswords.value = {};
    credentialFieldsList.forEach(field => {
      showPasswords.value[field] = false;
    });
  }
}, { immediate: true });

const formatFieldLabel = (key: string): string => {
  return fieldLabels[key] || key.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const getFieldPlaceholder = (key: string): string => {
  return "Введіть значення...";
};

const handleSubmit = async () => {
  if (!props.item?.type) return;

  isSaving.value = true;
  try {
    emit("save", props.item.type, formData.value);
  } finally {
    isSaving.value = false;
  }
};
</script>
