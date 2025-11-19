<template>
  <div class="space-y-4">
    <div class="p-4 bg-error-50 dark:bg-error-900/20 rounded-lg">
      <p class="text-sm text-error-600 dark:text-error-400">
        Ви збираєтеся видалити {{ fileIds.length }} {{ getFileWord(fileIds.length) }}
      </p>
    </div>

    <p class="text-sm text-gray-600 dark:text-gray-400">
      Ця дія незворотна. Всі вибрані файли будуть видалені з системи.
    </p>

    <USeparator />

    <div class="flex justify-end gap-2">
      <UButton type="button" variant="outline" color="neutral" @click="emits('close')">
        <template #leading>
          <Ban class="w-4 h-4" />
        </template>
        Скасувати
      </UButton>
      <UButton
        type="button"
        color="error"
        :loading="loading"
        @click="onDelete"
      >
        <template #leading>
          <Send class="w-4 h-4" />
        </template>
        Підтвердити
      </UButton>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Send, Ban } from "lucide-vue-next";

interface IProps {
  fileIds: number[];
}

interface IEmits {
  (e: "close"): void;
}

const props = defineProps<IProps>();
const emits = defineEmits<IEmits>();
const toast = useToast();

const { deleteFiles } = useFiles();

const loading = ref(false);

const getFileWord = (count: number): string => {
  if (count === 1) return "файл";
  if (count >= 2 && count <= 4) return "файли";
  return "файлів";
};

const onDelete = async () => {
  if (!props.fileIds || props.fileIds.length === 0) return;

  try {
    loading.value = true;

    await deleteFiles(props.fileIds);

    toast.add({
      title: "Успішно",
      description: `${getFileWord(props.fileIds.length)} успішно видалено`,
      color: "success",
    });

    emits("close");
  } catch (error: any) {
    toast.add({
      title: "Помилка",
      description: error?.message || "Не вдалося видалити файли",
      color: "error",
    });
  } finally {
    loading.value = false;
  }
};
</script>
