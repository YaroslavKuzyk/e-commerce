<template>
  <div class="space-y-4">
    <div v-if="file" class="p-4 bg-error-50 dark:bg-error-900/20 rounded-lg">
      <p class="text-sm text-error-600 dark:text-error-400">
        Ви збираєтеся видалити файл:
      </p>
      <p class="font-semibold mt-2">{{ file.original_name }}</p>
      <p class="text-sm text-gray-600 dark:text-gray-400">
        Розмір: {{ formatFileSize(file.size) }}
      </p>
    </div>

    <p class="text-sm text-gray-600 dark:text-gray-400">
      Ця дія незворотна. Файл буде видалено з системи.
    </p>

    <USeparator />

    <div class="flex justify-end gap-2">
      <UButton type="button" variant="outline" @click="emits('close')">
        Скасувати
      </UButton>
      <UButton
        type="button"
        color="error"
        :loading="loading"
        @click="onDelete"
      >
        Видалити
      </UButton>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { IFile } from "~/models/files";

interface IProps {
  file: IFile | null;
}

interface IEmits {
  (e: "close"): void;
}

const props = defineProps<IProps>();
const emits = defineEmits<IEmits>();
const toast = useToast();

const { deleteFile } = useFiles();

const loading = ref(false);

const onDelete = async () => {
  if (!props.file) return;

  try {
    loading.value = true;

    await deleteFile(props.file.id);

    toast.add({
      title: "Успішно",
      description: "Файл успішно видалено",
      color: "success",
    });

    emits("close");
  } catch (error: any) {
    toast.add({
      title: "Помилка",
      description: error?.message || "Не вдалося видалити файл",
      color: "error",
    });
  } finally {
    loading.value = false;
  }
};

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return "0 Bytes";
  const k = 1024;
  const sizes = ["Bytes", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + " " + sizes[i];
};
</script>
