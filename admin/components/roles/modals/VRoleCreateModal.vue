<template>
  <UModal
    title="Створити нову роль"
    description="Створіть нову роль для адміністратора."
    v-model:open="isOpen"
  >
    <template #body>
      <VRoleCreateForm @close="isOpen = false" />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VRoleCreateForm from "@/components/roles/forms/VRoleCreateForm.vue";

const isOpen = defineModel<boolean>("isOpen");
</script>
