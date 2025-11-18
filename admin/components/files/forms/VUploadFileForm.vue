<template>
  <UCard>
    <div class="space-y-4">
      <div
        class="border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-lg p-8 text-center cursor-pointer hover:border-primary-500 transition-colors"
        @click="triggerFileInput"
        @drop.prevent="handleDrop"
        @dragover.prevent
      >
        <UploadCloud class="w-12 h-12 mx-auto mb-4 text-gray-400" />
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
          Клікніть для вибору файлу або перетягніть сюди
        </p>
        <p class="text-xs text-gray-500 dark:text-gray-500">
          Максимальний розмір: 10MB
        </p>
        <input
          ref="fileInput"
          type="file"
          class="hidden"
          @change="handleFileSelect"
        />
      </div>

      <div
        v-if="selectedFile"
        class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
      >
        <FileIcon class="w-5 h-5 text-gray-500" />
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
            {{ selectedFile.name }}
          </p>
          <p class="text-xs text-gray-500">
            {{ formatFileSize(selectedFile.size) }}
          </p>
        </div>
        <UButton
          color="neutral"
          variant="ghost"
          size="sm"
          @click="clearSelectedFile"
        >
          <X class="w-4 h-4" />
        </UButton>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-2">
        <UButton
          color="neutral"
          variant="ghost"
          @click="emits('close')"
          :disabled="isUploading"
        >
          Скасувати
        </UButton>
        <UButton
          color="primary"
          @click="uploadFile"
          :loading="isUploading"
          :disabled="!selectedFile"
        >
          Завантажити
        </UButton>
      </div>
    </template>
  </UCard>
</template>

<script setup lang="ts">
import { UploadCloud, File as FileIcon, X } from "lucide-vue-next";

interface IEmits {
  (e: "close"): void;
}

const emits = defineEmits<IEmits>();

const { uploadFile: uploadFileService } = useFiles();
const toast = useToast();

const selectedFile = ref<File | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const isUploading = ref(false);

const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    selectedFile.value = target.files[0];
  }
};

const handleDrop = (event: DragEvent) => {
  if (event.dataTransfer?.files && event.dataTransfer.files[0]) {
    selectedFile.value = event.dataTransfer.files[0];
  }
};

const clearSelectedFile = () => {
  selectedFile.value = null;
  if (fileInput.value) {
    fileInput.value.value = "";
  }
};

const uploadFile = async () => {
  if (!selectedFile.value) return;

  try {
    isUploading.value = true;

    await uploadFileService({ file: selectedFile.value });

    toast.add({
      title: "Успішно",
      description: "Файл успішно завантажено",
    });

    clearSelectedFile();
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
