<template>
  <UModal
    title="Управління методами оплати"
    :description="`Оберіть доступні методи оплати для: ${
      props.deliveryMethod?.name_uk || props.deliveryMethod?.name || ''
    }`"
    v-model:open="isOpen"
  >
    <template #body>
      <VManagePaymentMethodsForm
        :delivery-method="props.deliveryMethod"
        :payment-methods="props.paymentMethods"
        @close="closeAndRefresh"
        @refresh="emits('refresh')"
      />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VManagePaymentMethodsForm from "~/components/delivery-payment/forms/VManagePaymentMethodsForm.vue";
import type { DeliveryMethod } from "~/models/deliveryMethod";
import type { PaymentMethod } from "~/models/paymentMethod";

interface IProps {
  deliveryMethod?: DeliveryMethod | null;
  paymentMethods?: PaymentMethod[];
}

interface IEmits {
  (e: "refresh"): void;
}

const props = defineProps<IProps>();
const emits = defineEmits<IEmits>();

const isOpen = defineModel<boolean>();

const closeAndRefresh = () => {
  isOpen.value = false;
};
</script>
