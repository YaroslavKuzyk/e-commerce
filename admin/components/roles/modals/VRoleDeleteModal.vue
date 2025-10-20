<template>
  <UModal
    title="Видалити роль"
    description="Видалити роль для адміністратора."
    v-model:open="isOpen"
  >
    <template #body>
      <VRoleDeleteForm @close="isOpen = false" />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VRoleDeleteForm from "@/components/roles/forms/VRoleDeleteForm.vue";

const isOpen = defineModel<boolean>("isOpen");
</script>
