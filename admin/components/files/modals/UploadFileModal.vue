<template>
  <UModal
    v-model:open="isOpen"
    title="Завантажити файл"
    description="Додайте файл до системи."
  >
    <template #body>
      <VUploadFileForm @close="closeAndRefresh" />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VUploadFileForm from "@/components/files/forms/VUploadFileForm.vue";

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
