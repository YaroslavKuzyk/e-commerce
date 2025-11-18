<template>
  <UModal
    v-model:open="isOpen"
    title="Видалити файл"
    description="Підтвердіть видалення файлу."
  >
    <template #body>
      <VFileDeleteForm :file="file" @close="closeAndRefresh" />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VFileDeleteForm from "@/components/files/forms/VFileDeleteForm.vue";
import type { IFile } from "~/models/files";

interface IProps {
  file: IFile | null;
}

interface IEmits {
  (e: "refresh"): void;
}

const props = defineProps<IProps>();
const emits = defineEmits<IEmits>();

const isOpen = defineModel<boolean>("isOpen");

const closeAndRefresh = () => {
  emits("refresh");
  isOpen.value = false;
};
</script>
