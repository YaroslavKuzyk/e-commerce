<template>
  <UModal
    title="Видалити роль"
    description="Видалити роль для адміністратора."
    v-model:open="isOpen"
  >
    <template #body>
      <VRoleDeleteForm @close="closeAndRefresh" />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VRoleDeleteForm from "@/components/roles/forms/VRoleDeleteForm.vue";

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
