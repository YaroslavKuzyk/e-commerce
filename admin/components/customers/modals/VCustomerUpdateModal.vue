<template>
  <UModal
    title="Редагувати покупця"
    description="Оновіть інформацію про покупця."
    v-model:open="isOpen"
  >
    <template #body>
      <VCustomerUpdateForm :customer="customer" @close="closeAndRefresh" />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VCustomerUpdateForm from "@/components/customers/forms/VCustomerUpdateForm.vue";
import type { ICustomer } from "~/models/customers";

interface IProps {
  customer: ICustomer | null;
}

interface IEmits {
  (e: "refresh"): void;
}

defineProps<IProps>();
const emits = defineEmits<IEmits>();

const isOpen = defineModel<boolean>("isOpen");

const closeAndRefresh = () => {
  emits("refresh");
  isOpen.value = false;
};
</script>
