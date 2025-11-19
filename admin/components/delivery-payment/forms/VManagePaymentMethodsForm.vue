<template>
  <div class="space-y-4">
    <div class="space-y-2">
      <div
        v-for="pm in paymentMethods"
        :key="pm.id"
        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded"
        :class="{ 'opacity-50': !pm.is_active }"
      >
        <div class="flex-1">
          <div class="font-medium">
            {{ pm.name_uk || pm.name }}
            <span v-if="!pm.is_active" class="text-xs text-red-500 ml-2">
              (Вимкнено в системі)
            </span>
          </div>
          <div class="text-sm text-gray-500">{{ pm.code }}</div>
        </div>
        <USwitch
          :model-value="
            pm.is_active &&
            getPaymentMethodStatus(pm.id).linked &&
            getPaymentMethodStatus(pm.id).active
          "
          :disabled="!pm.is_active"
          @update:model-value="togglePaymentMethod(pm.id)"
        />
      </div>
    </div>

    <USeparator />

    <div class="flex justify-end gap-2">
      <UButton type="button" variant="outline" color="neutral" @click="emits('close')">
        <template #leading>
          <X class="w-4 h-4" />
        </template>
        Закрити
      </UButton>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { X } from "lucide-vue-next";
import type { DeliveryMethod } from "~/models/deliveryMethod";
import type { PaymentMethod } from "~/models/paymentMethod";

interface IProps {
  deliveryMethod?: DeliveryMethod | null;
  paymentMethods?: PaymentMethod[];
}

interface IEmits {
  (e: "close"): void;
  (e: "refresh"): void;
}

const props = defineProps<IProps>();
const emits = defineEmits<IEmits>();
const toast = useToast();

const deliveryMethodStore = useDeliveryMethodStore();

// Local state for optimistic UI updates
const localPaymentMethodsState = ref<
  Map<number, { linked: boolean; active: boolean }>
>(new Map());

// Get current delivery method directly from props
const currentDeliveryMethod = computed(() => {
  return props.deliveryMethod || null;
});

// Track selected payment methods (linked to this delivery method)
const selectedPaymentMethodIds = ref<Set<number>>(new Set());

// Initialize selected payment methods when delivery method changes
watchEffect(() => {
  if (
    currentDeliveryMethod.value &&
    currentDeliveryMethod.value.payment_methods
  ) {
    selectedPaymentMethodIds.value = new Set(
      currentDeliveryMethod.value.payment_methods.map((pm) => pm.id)
    );

    // Initialize local state from server data
    localPaymentMethodsState.value.clear();
    currentDeliveryMethod.value.payment_methods.forEach((pm) => {
      localPaymentMethodsState.value.set(pm.id, {
        linked: true,
        active: pm.pivot_is_active ?? true,
      });
    });
  }
});

// Get payment method status (linked and active) for the current delivery method
const getPaymentMethodStatus = (paymentMethodId: number) => {
  // Check local state first for optimistic UI
  if (localPaymentMethodsState.value.has(paymentMethodId)) {
    return localPaymentMethodsState.value.get(paymentMethodId)!;
  }

  const isLinked = selectedPaymentMethodIds.value.has(paymentMethodId);

  if (!isLinked) {
    return { linked: false, active: false };
  }

  if (
    !currentDeliveryMethod.value ||
    !currentDeliveryMethod.value.payment_methods
  ) {
    return { linked: false, active: false };
  }

  const linkedPaymentMethod = currentDeliveryMethod.value.payment_methods.find(
    (pm) => pm.id === paymentMethodId
  );

  return {
    linked: true,
    active: linkedPaymentMethod?.pivot_is_active ?? true,
  };
};

// Toggle payment method - either add/remove or toggle active status
const togglePaymentMethod = async (paymentMethodId: number) => {
  if (!props.deliveryMethod) return;

  const status = getPaymentMethodStatus(paymentMethodId);

  // Optimistic UI update - update local state immediately
  if (!status.linked) {
    // Adding new payment method
    localPaymentMethodsState.value.set(paymentMethodId, {
      linked: true,
      active: true,
    });
    selectedPaymentMethodIds.value.add(paymentMethodId);
  } else {
    // Toggle active status
    localPaymentMethodsState.value.set(paymentMethodId, {
      linked: true,
      active: !status.active,
    });
  }

  try {
    // If not linked, add it (sync will add with is_active = true)
    if (!status.linked) {
      await deliveryMethodStore.onSyncPaymentMethods(
        props.deliveryMethod.id,
        Array.from(selectedPaymentMethodIds.value)
      );

      toast.add({
        title: "Успіх",
        description: "Метод оплати додано",
        color: "success",
      });
    } else {
      // Toggle its active status
      await deliveryMethodStore.onTogglePaymentMethodActive(
        props.deliveryMethod.id,
        paymentMethodId
      );

      toast.add({
        title: "Успіх",
        description: status.active
          ? "Метод оплати вимкнено"
          : "Метод оплати увімкнено",
        color: "success",
      });
    }
  } catch (error: any) {
    // Revert optimistic update on error
    if (!status.linked) {
      localPaymentMethodsState.value.delete(paymentMethodId);
      selectedPaymentMethodIds.value.delete(paymentMethodId);
    } else {
      localPaymentMethodsState.value.set(paymentMethodId, {
        linked: true,
        active: status.active,
      });
    }

    toast.add({
      title: "Помилка",
      description: error.message || "Не вдалося змінити метод оплати",
      color: "error",
    });
  } finally {
    emits("refresh");
  }
};
</script>
