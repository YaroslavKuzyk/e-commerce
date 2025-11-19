<template>
  <div class="flex flex-col gap-4">
    <div class="space-y-4 flex flex-col">
      <div
        class="border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-lg p-8 text-center cursor-pointer hover:border-primary-500 transition-colors"
        @click="triggerFileInput"
        @drop.prevent="handleDrop"
        @dragover.prevent
      >
        <UploadCloud class="w-12 h-12 mx-auto mb-4 text-gray-400" />
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
          Клікніть для вибору {{ maxFiles > 1 ? 'файлів' : 'файлу' }} або перетягніть сюди
        </p>
        <p class="text-xs text-gray-500 dark:text-gray-500">
          Максимальний розмір: 10MB{{ maxFiles > 1 ? ` | Максимум файлів: ${maxFiles}` : '' }}
        </p>
        <input
          ref="fileInput"
          type="file"
          class="hidden"
          :multiple="maxFiles > 1"
          @change="handleFileSelect"
        />
      </div>

      <div
        v-if="selectedFiles.length > 0"
        class="space-y-2"
      >
        <div
          v-for="(file, index) in selectedFiles"
          :key="index"
          class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
        >
          <FileIcon class="w-5 h-5 text-gray-500" />
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
              {{ file.name }}
            </p>
            <p class="text-xs text-gray-500">
              {{ formatFileSize(file.size) }}
            </p>
          </div>
          <UButton
            color="neutral"
            variant="ghost"
            size="sm"
            @click="removeFile(index)"
          >
            <X class="w-4 h-4" />
          </UButton>
        </div>
      </div>
    </div>

    <USeparator />

    <div class="flex justify-end gap-2">
      <div class="flex justify-end gap-2">
        <UButton
          variant="outline"
          color="neutral"
          @click="emits('close')"
          :disabled="isUploading"
        >
          <template #leading>
            <Ban class="w-4 h-4" />
          </template>
          Скасувати
        </UButton>
        <UButton
          color="primary"
          @click="uploadFile"
          :loading="isUploading"
          :disabled="selectedFiles.length === 0"
        >
          <template #leading>
            <Send class="w-4 h-4" />
          </template>
          Підтвердити{{ selectedFiles.length > 1 ? ` (${selectedFiles.length})` : '' }}
        </UButton>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { UploadCloud, File as FileIcon, X, Send, Ban } from "lucide-vue-next";

interface IProps {
  maxFiles?: number;
}

interface IEmits {
  (e: "close"): void;
}

const props = withDefaults(defineProps<IProps>(), {
  maxFiles: 15,
});

const emits = defineEmits<IEmits>();

const { uploadFile: uploadFileService } = useFiles();
const toast = useToast();

const selectedFiles = ref<File[]>([]);
const fileInput = ref<HTMLInputElement | null>(null);
const isUploading = ref(false);

const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files) {
    addFiles(Array.from(target.files));
  }
};

const handleDrop = (event: DragEvent) => {
  if (event.dataTransfer?.files) {
    addFiles(Array.from(event.dataTransfer.files));
  }
};

const addFiles = (files: File[]) => {
  const remainingSlots = props.maxFiles - selectedFiles.value.length;

  if (remainingSlots <= 0) {
    toast.add({
      title: "Помилка",
      description: `Ви вже вибрали максимальну кількість файлів (${props.maxFiles})`,
      color: "error",
    });
    return;
  }

  const filesToAdd = files.slice(0, remainingSlots);

  if (files.length > remainingSlots) {
    toast.add({
      title: "Попередження",
      description: `Додано ${filesToAdd.length} з ${files.length} файлів. Максимум: ${props.maxFiles}`,
      color: "warning",
    });
  }

  selectedFiles.value.push(...filesToAdd);
};

const removeFile = (index: number) => {
  selectedFiles.value.splice(index, 1);
  if (fileInput.value) {
    fileInput.value.value = "";
  }
};

const clearSelectedFiles = () => {
  selectedFiles.value = [];
  if (fileInput.value) {
    fileInput.value.value = "";
  }
};

const uploadFile = async () => {
  if (selectedFiles.value.length === 0) return;

  try {
    isUploading.value = true;

    // Завантажуємо всі файли по черзі
    for (const file of selectedFiles.value) {
      await uploadFileService({ file });
    }

    toast.add({
      title: "Успішно",
      description: `${selectedFiles.value.length > 1 ? 'Файли' : 'Файл'} успішно завантажено`,
    });

    clearSelectedFiles();
    emits("close");
  } catch (error: any) {
    console.error("Error uploading file:", error);
    toast.add({
      title: "Помилка",
      description: error?.data?.message || "Не вдалося завантажити файл",
      color: "error",
    });
  } finally {
    isUploading.value = false;
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
