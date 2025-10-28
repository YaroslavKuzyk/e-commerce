<template>
  <UModal
    title="Створити нового покупця"
    description="Додайте нового покупця до системи."
    v-model:open="isOpen"
  >
    <template #body>
      <VCustomerCreateForm @close="closeAndRefresh" />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VCustomerCreateForm from "@/components/customers/forms/VCustomerCreateForm.vue";

interface IEmits {
  (e: "refresh"): void;
}

const emits = defineEmits<IEmits>();

const isOpen = defineModel<boolean>("isOpen");

const closeAndRefresh = () => {
  emits("refresh");
  isOpen.value = false;
};
</script>
