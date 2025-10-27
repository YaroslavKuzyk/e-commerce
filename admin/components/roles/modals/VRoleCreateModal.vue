<template>
  <UModal
    title="Створити нову роль"
    description="Створіть нову роль для адміністратора."
    v-model:open="isOpen"
  >
    <template #body>
      <VRoleCreateForm @close="closeAndRefresh" />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VRoleCreateForm from "@/components/roles/forms/VRoleCreateForm.vue";

interface IEmits {
  (e: "refresh"): void;
}

const emits = defineEmits<IEmits>();

const isOpen = defineModel<boolean>("isOpen");

const closeAndRefresh = async () => {
  await emits("refresh");
  isOpen.value = false;
};
</script>
