<template>
  <UModal
    title="Видалити покупця"
    description="Ви впевнені, що хочете видалити цього покупця?"
    v-model:open="isOpen"
  >
    <template #body>
      <VCustomerDeleteForm :customer="customer" @close="closeAndRefresh" />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VCustomerDeleteForm from "@/components/customers/forms/VCustomerDeleteForm.vue";
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
