<template>
  <UModal
    v-model:open="isOpen"
    title="Видалення файлів"
    description="Підтвердіть видалення вибраних файлів."
  >
    <template #body>
      <VFileBulkDeleteForm
        :file-ids="fileIds"
        @close="closeAndRefresh"
      />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VFileBulkDeleteForm from "@/components/files/forms/VFileBulkDeleteForm.vue";

interface IProps {
  fileIds: number[];
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
