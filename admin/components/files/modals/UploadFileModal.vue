<template>
  <UModal
    v-model:open="isOpen"
    :title="maxFiles > 1 ? 'Завантажити файли' : 'Завантажити файл'"
    :description="maxFiles > 1 ? `Додайте до ${maxFiles} файлів до системи.` : 'Додайте файл до системи.'"
  >
    <template #body>
      <VUploadFileForm :max-files="maxFiles" @close="closeAndRefresh" />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VUploadFileForm from "@/components/files/forms/VUploadFileForm.vue";

interface IProps {
  maxFiles?: number;
}

interface IEmits {
  (e: "refresh"): void;
}

const props = withDefaults(defineProps<IProps>(), {
  maxFiles: 15,
});

const emits = defineEmits<IEmits>();

const isOpen = defineModel<boolean>("isOpen");

const closeAndRefresh = () => {
  emits("refresh");
  isOpen.value = false;
};
</script>
