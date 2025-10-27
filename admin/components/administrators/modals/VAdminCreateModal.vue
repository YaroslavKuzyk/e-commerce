<template>
  <UModal
    title="Створити нового адміністратора"
    description="Додайте нового адміністратора до системи."
    v-model:open="isOpen"
  >
    <template #body>
      <VAdminCreateForm @close="closeAndRefresh" />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VAdminCreateForm from "@/components/administrators/forms/VAdminCreateForm.vue";

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
