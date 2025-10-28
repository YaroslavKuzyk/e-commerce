<template>
  <UModal
    :title="props.paymentMethod ? 'Редагувати метод оплати' : 'Створити метод оплати'"
    :description="
      props.paymentMethod
        ? 'Оновіть дані методу оплати'
        : 'Додайте новий метод оплати до системи'
    "
    v-model:open="isOpen"
  >
    <template #body>
      <VPaymentMethodForm :payment-method="props.paymentMethod" @close="closeAndRefresh" />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VPaymentMethodForm from "~/components/delivery-payment/forms/VPaymentMethodForm.vue";
import type { PaymentMethod } from "~/models/paymentMethod";

interface IProps {
  paymentMethod?: PaymentMethod | null;
}

interface IEmits {
  (e: "refresh"): void;
}

const props = defineProps<IProps>();
const emits = defineEmits<IEmits>();

const isOpen = defineModel<boolean>();

const closeAndRefresh = () => {
  emits("refresh");
  isOpen.value = false;
};
</script>
