<template>
  <VSidebarContent title="Оплата та Доставка">
    <template #toolbar>
      <div>
        <UTabs v-model="selectedTab" :items="tabs" class="gap-0" />
      </div>
    </template>

    <!-- Delivery Methods Tab -->
    <HasPermissions
      v-if="selectedTab === '0'"
      :required-permissions="['Read Delivery Methods']"
    >
      <DeliveryMethodsTable
        :delivery-methods="deliveryMethods"
        :loading="deliveryLoading"
        @toggle-active="toggleDeliveryActive"
        @edit="openDeliveryMethodModal"
        @manage-payment-methods="openManagePaymentMethods"
      />
    </HasPermissions>

    <!-- Payment Methods Tab -->
    <HasPermissions
      v-if="selectedTab === '1'"
      :required-permissions="['Read Payment Methods']"
    >
      <PaymentMethodsTable
        :payment-methods="paymentMethods"
        :loading="paymentLoading"
        @toggle-active="togglePaymentActive"
      />
    </HasPermissions>

    <!-- Modals -->
    <VDeliveryMethodModal
      v-model="isDeliveryMethodModalOpen"
      :delivery-method="selectedDeliveryMethodForEdit"
      @refresh="refreshDeliveryMethods"
    />

    <VManagePaymentMethodsModal
      v-model="isManagePaymentMethodsOpen"
      :delivery-method="selectedDeliveryMethod"
      :payment-methods="paymentMethods"
      @refresh="refreshDeliveryMethods"
    />
  </VSidebarContent>
</template>

<script setup lang="ts">
import HasPermissions from "~/components/common/VHasPermissions.vue";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VDeliveryMethodModal from "~/components/delivery-payment/modals/VDeliveryMethodModal.vue";
import VManagePaymentMethodsModal from "~/components/delivery-payment/modals/VManagePaymentMethodsModal.vue";
import DeliveryMethodsTable from "~/components/delivery-payment/tables/DeliveryMethodsTable.vue";
import PaymentMethodsTable from "~/components/delivery-payment/tables/PaymentMethodsTable.vue";
import type { DeliveryMethod } from "~/models/deliveryMethod";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Read Delivery Methods", "Read Payment Methods"],
});

const deliveryMethodStore = useDeliveryMethodStore();
const paymentMethodStore = usePaymentMethodStore();
const toast = useToast();

const selectedTab = ref<string>("0");

const tabs = [
  {
    slot: "delivery",
    label: "Методи доставки",
    icon: "i-heroicons-truck",
  },
  {
    slot: "payment",
    label: "Методи оплати",
    icon: "i-heroicons-credit-card",
  },
];

// Delivery Methods
const {
  data: deliveryMethodsData,
  refresh: refreshDeliveryMethodsData,
  status: deliveryStatus,
} = await deliveryMethodStore.fetchDeliveryMethods();

// Local mutable copy for optimistic updates
const deliveryMethods = ref(deliveryMethodsData.value || []);

// Sync local copy with server data
watch(deliveryMethodsData, (newData) => {
  if (newData) {
    deliveryMethods.value = [...newData];
  }
});

const deliveryLoading = computed(() => deliveryStatus.value === "pending");

const refreshDeliveryMethods = async () => {
  await refreshDeliveryMethodsData();
};

// Payment Methods
const {
  data: paymentMethodsData,
  refresh: refreshPaymentMethodsData,
  status: paymentStatus,
} = await paymentMethodStore.fetchPaymentMethods();

// Local mutable copy for optimistic updates
const paymentMethods = ref(paymentMethodsData.value || []);

// Sync local copy with server data
watch(paymentMethodsData, (newData) => {
  if (newData) {
    paymentMethods.value = [...newData];
  }
});

const paymentLoading = computed(() => paymentStatus.value === "pending");

const refreshPaymentMethods = async () => {
  await refreshPaymentMethodsData();
};

// Delivery Method Actions
const selectedDeliveryMethod = ref<DeliveryMethod | null>(null);
const selectedDeliveryMethodForEdit = ref<DeliveryMethod | null>(null);
const isDeliveryMethodModalOpen = ref(false);

const openDeliveryMethodModal = (deliveryMethod: DeliveryMethod) => {
  selectedDeliveryMethodForEdit.value = deliveryMethod;
  isDeliveryMethodModalOpen.value = true;
};

const toggleDeliveryActive = async (id: number) => {
  if (!deliveryMethods.value) return;

  // Optimistic UI update
  const method = deliveryMethods.value.find((dm) => dm.id === id);
  if (!method) return;

  const previousValue = method.is_active;
  method.is_active = !previousValue;

  try {
    await deliveryMethodStore.onToggleDeliveryMethodActive(id);
    toast.add({
      title: "Успіх",
      description: "Статус методу доставки змінено",
      color: "success",
    });
    // Refresh to sync with server
    await refreshDeliveryMethods();
  } catch (error) {
    // Revert on error
    method.is_active = previousValue;
    toast.add({
      title: "Помилка",
      description: "Не вдалося змінити статус",
      color: "error",
    });
  }
};

// Payment Method Actions
const togglePaymentActive = async (id: number) => {
  if (!paymentMethods.value) return;

  // Optimistic UI update
  const method = paymentMethods.value.find((pm) => pm.id === id);
  if (!method) return;

  const previousValue = method.is_active;
  method.is_active = !previousValue;

  try {
    await paymentMethodStore.onTogglePaymentMethodActive(id);
    toast.add({
      title: "Успіх",
      description: "Статус методу оплати змінено",
      color: "success",
    });
    // Refresh to sync with server
    await refreshPaymentMethods();
  } catch (error) {
    // Revert on error
    method.is_active = previousValue;
    toast.add({
      title: "Помилка",
      description: "Не вдалося змінити статус",
      color: "error",
    });
  }
};

// Manage Payment Methods Modal
const isManagePaymentMethodsOpen = ref(false);

const openManagePaymentMethods = (deliveryMethod: DeliveryMethod) => {
  selectedDeliveryMethod.value = deliveryMethod;
  isManagePaymentMethodsOpen.value = true;
};
</script>
