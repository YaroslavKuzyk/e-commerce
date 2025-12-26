<template>
  <VSidebarContent title="Системні налаштування">
    <template #toolbar>
      <div>
        <UTabs v-model="selectedTab" :items="tabs" class="gap-0" />
      </div>
    </template>

    <HasPermissions :required-permissions="['Read System Settings']">
      <div v-if="isLoading" class="flex items-center justify-center py-12">
        <UIcon name="i-heroicons-arrow-path" class="animate-spin text-2xl text-gray-400" />
      </div>

      <div v-else-if="types" class="space-y-6 p-6">
        <!-- Delivery Integrations -->
        <div v-if="selectedTab === '0'" class="space-y-4">
          <VSystemSettingCard
            v-for="item in typesByCategory.delivery"
            :key="item.type"
            :item="item"
            @edit="openSettingModal"
            @toggle="handleToggle"
          />
          <div v-if="!typesByCategory.delivery?.length" class="text-center py-8 text-gray-500">
            Немає доступних інтеграцій доставки
          </div>
        </div>

        <!-- Payment Integrations -->
        <div v-if="selectedTab === '1'" class="space-y-4">
          <VSystemSettingCard
            v-for="item in typesByCategory.payment"
            :key="item.type"
            :item="item"
            @edit="openSettingModal"
            @toggle="handleToggle"
          />
          <div v-if="!typesByCategory.payment?.length" class="text-center py-8 text-gray-500">
            Немає доступних інтеграцій оплати
          </div>
        </div>

        <!-- SMS Integrations -->
        <div v-if="selectedTab === '2'" class="space-y-4">
          <VSystemSettingCard
            v-for="item in typesByCategory.sms"
            :key="item.type"
            :item="item"
            @edit="openSettingModal"
            @toggle="handleToggle"
          />
          <div v-if="!typesByCategory.sms?.length" class="text-center py-8 text-gray-500">
            Немає доступних інтеграцій SMS
          </div>
        </div>
      </div>
    </HasPermissions>

    <!-- Edit Modal -->
    <VSystemSettingModal
      v-model="isModalOpen"
      :item="selectedItem"
      @save="handleSave"
    />
  </VSidebarContent>
</template>

<script setup lang="ts">
import HasPermissions from "~/components/common/VHasPermissions.vue";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VSystemSettingCard from "~/components/system-settings/VSystemSettingCard.vue";
import VSystemSettingModal from "~/components/system-settings/VSystemSettingModal.vue";
import type { SystemSettingType, SystemSettingTypeInfo } from "~/models/systemSettings";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Read System Settings"],
});

const systemSettingsStore = useSystemSettingsStore();
const toast = useToast();

const selectedTab = ref<string>("0");

const tabs = [
  {
    slot: "delivery",
    label: "Доставка",
    icon: "i-heroicons-truck",
  },
  {
    slot: "payment",
    label: "Оплата",
    icon: "i-heroicons-credit-card",
  },
  {
    slot: "sms",
    label: "SMS",
    icon: "i-heroicons-chat-bubble-left-right",
  },
];

const isLoading = ref(true);

const types = computed(() => systemSettingsStore.types);
const typesByCategory = computed(() => systemSettingsStore.typesByCategory);

// Modal state
const isModalOpen = ref(false);
const selectedItem = ref<SystemSettingTypeInfo | null>(null);

const openSettingModal = (item: SystemSettingTypeInfo) => {
  selectedItem.value = item;
  isModalOpen.value = true;
};

const handleToggle = async (type: SystemSettingType, isActive: boolean) => {
  try {
    await systemSettingsStore.onToggleActive(type, isActive);
    toast.add({
      title: "Успіх",
      description: isActive ? "Інтеграцію активовано" : "Інтеграцію деактивовано",
      color: "success",
    });
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося змінити статус",
      color: "error",
    });
  }
};

const handleSave = async (type: SystemSettingType, data: Record<string, string>) => {
  try {
    await systemSettingsStore.onUpdateSetting(type, { data });
    toast.add({
      title: "Успіх",
      description: "Налаштування збережено",
      color: "success",
    });
    isModalOpen.value = false;
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося зберегти налаштування",
      color: "error",
    });
  }
};

// Fetch data on mount
onMounted(async () => {
  try {
    await systemSettingsStore.fetchTypes();
  } finally {
    isLoading.value = false;
  }
});
</script>
