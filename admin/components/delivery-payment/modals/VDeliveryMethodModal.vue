<template>
  <UModal
    title="Редагувати метод доставки"
    description="Оновіть дані методу доставки"
    v-model:open="isOpen"
  >
    <template #body>
      <VDeliveryMethodForm
        :delivery-method="props.deliveryMethod"
        @close="closeAndRefresh"
      />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VDeliveryMethodForm from "~/components/delivery-payment/forms/VDeliveryMethodForm.vue";
import type { DeliveryMethod } from "~/models/deliveryMethod";

interface IProps {
  deliveryMethod: DeliveryMethod | null;
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
