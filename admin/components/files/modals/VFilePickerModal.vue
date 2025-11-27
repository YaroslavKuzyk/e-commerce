<template>
  <UModal
    v-model:open="isOpen"
    :title="maxFiles > 1 ? 'Вибрати файли' : 'Вибрати файл'"
    :description="
      maxFiles > 1
        ? `Виберіть до ${maxFiles} файлів або завантажте нові`
        : 'Виберіть файл або завантажте новий'
    "
    class="max-w-screen-xl"
  >
    <template #body>
      <div
        class="flex flex-col gap-4"
        @drop.prevent="handleDrop"
        @dragenter.prevent="handleDragEnter"
        @dragover.prevent
        @dragleave.prevent="handleDragLeave"
      >
        <!-- Filters and Upload -->
        <div class="flex items-center gap-2 flex-wrap">
          <UInput
            v-model="filters.search"
            placeholder="Пошук за назвою файлу"
            class="w-[250px]"
          >
            <template #leading>
              <Search class="w-5 h-5" />
            </template>
          </UInput>
          <UInput
            v-model="filters.user_search"
            placeholder="Користувач"
            class="w-[200px]"
          >
            <template #leading>
              <User class="w-5 h-5" />
            </template>
          </UInput>
          <USelectMenu
            v-if="!fileType"
            v-model="selectedTypesObjects"
            :items="fileTypeOptions"
            multiple
            placeholder="Тип файлу"
            value-key="value"
            label-key="label"
            class="w-[150px]"
          >
            <template #trailing>
              <UButton
                v-if="selectedTypesObjects.length > 0"
                size="sm"
                variant="link"
                aria-label="Очистити"
                @click.stop="selectedTypesObjects = []"
                color="neutral"
              >
                <CircleX class="w-4 h-4" />
              </UButton>
            </template>
          </USelectMenu>
          <UButton
            v-if="hasNonFileTypeFilters"
            variant="ghost"
            @click="resetFilters"
          >
            <template #leading>
              <X class="w-5 h-5" />
            </template>
            Очистити фільтри
          </UButton>
          <div class="flex-1"></div>
          <UButton
            color="primary"
            @click="triggerFileInput"
            :loading="isUploading"
          >
            <template #leading>
              <Upload class="w-4 h-4" />
            </template>
            Завантажити файли
          </UButton>
          <input
            ref="fileInput"
            type="file"
            class="hidden"
            :multiple="maxFiles > 1"
            :accept="fileType ? getAcceptAttribute(fileType) : undefined"
            @change="handleFileSelect"
          />
        </div>

        <!-- Drag & Drop Overlay -->
        <div
          v-if="isDragging"
          class="fixed inset-0 z-50 bg-primary-500/20 backdrop-blur-sm flex items-center justify-center pointer-events-none"
        >
          <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl p-12 text-center"
          >
            <Upload class="w-24 h-24 mx-auto mb-4 text-primary-500" />
            <p class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
              Перетягніть файли сюди
            </p>
            <p class="text-gray-500 dark:text-gray-400">
              {{ maxFiles > 1 ? `До ${maxFiles} файлів` : "1 файл" }}
            </p>
          </div>
        </div>

        <!-- Main Content: 2 columns -->
        <div class="flex gap-4 h-[600px]">
          <!-- Left: Files Grid (80%) -->
          <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Loading State -->
            <div
              v-if="isLoading"
              class="flex items-center justify-center flex-1"
            >
              <Loader2 class="w-8 h-8 animate-spin text-gray-400" />
            </div>

            <!-- Empty State -->
            <div
              v-else-if="!files || (files.length === 0 && !hasActiveFilters)"
              class="flex items-center justify-center flex-1"
            >
              <div class="text-center">
                <FolderOpen
                  class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-700"
                />
                <p class="text-gray-500 dark:text-gray-400 mb-4">
                  Файлів поки немає
                </p>
                <UButton color="primary" @click="triggerFileInput">
                  <template #leading>
                    <Upload class="w-4 h-4" />
                  </template>
                  Завантажити перший файл
                </UButton>
              </div>
            </div>

            <!-- No Results -->
            <div
              v-else-if="!files || (files.length === 0 && hasActiveFilters)"
              class="flex items-center justify-center flex-1"
            >
              <div class="text-center">
                <Filter
                  class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-700"
                />
                <p class="text-gray-500 dark:text-gray-400 mb-4">
                  За заданими фільтрами файлів не знайдено
                </p>
                <UButton
                  color="neutral"
                  variant="outline"
                  @click="resetFilters"
                >
                  Очистити фільтри
                </UButton>
              </div>
            </div>

            <!-- Files Grid -->
            <div
              v-else-if="files && files.length > 0"
              class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 overflow-y-auto p-1"
            >
              <div
                v-for="file in files"
                :key="file.id"
                class="relative bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden hover:shadow-lg transition-all cursor-pointer group"
                :class="{
                  'ring-2 ring-primary-500': isFileSelected(file.id),
                  'ring-2 ring-blue-500':
                    selectedFileForDetails?.id === file.id,
                }"
                @click="selectFileForDetails(file)"
              >
                <!-- Checkbox -->
                <div class="absolute top-2 left-2 z-10" @click.stop>
                  <UCheckbox
                    :model-value="isFileSelected(file.id)"
                    @update:model-value="toggleFileSelection(file)"
                  />
                </div>

                <!-- File Preview -->
                <div
                  class="aspect-square bg-gray-100 dark:bg-gray-700 flex items-center justify-center overflow-hidden"
                >
                  <VSecureImage
                    v-if="isImage(file.mime_type)"
                    :file-id="file.id"
                    :alt="file.original_name"
                    width="w-full"
                    height="h-full"
                    object-fit="cover"
                  />
                  <div
                    v-else
                    class="flex flex-col items-center justify-center p-4"
                  >
                    <component
                      :is="getFileIcon(file.mime_type)"
                      class="w-12 h-12 text-gray-400 mb-1"
                    />
                    <p
                      class="text-xs text-gray-500 text-center truncate w-full"
                    >
                      {{ getFileExtension(file.original_name) }}
                    </p>
                  </div>
                </div>

                <!-- File Name (on hover) -->
                <div
                  class="absolute bottom-0 left-0 right-0 bg-black/70 text-white p-2 opacity-0 group-hover:opacity-100 transition-opacity"
                >
                  <p class="text-xs truncate">{{ file.original_name }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right: File Details (20%) -->
          <div
            class="w-80 flex flex-col bg-gray-50 dark:bg-gray-800 rounded-lg p-4 overflow-y-auto"
          >
            <div
              v-if="!selectedFileForDetails"
              class="flex items-center justify-center h-full text-center"
            >
              <div>
                <Info
                  class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600"
                />
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Оберіть файл для перегляду деталей
                </p>
              </div>
            </div>

            <div v-else class="space-y-4">
              <!-- Preview -->
              <div
                class="aspect-square bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden flex items-center justify-center"
              >
                <VSecureImage
                  v-if="isImage(selectedFileForDetails.mime_type)"
                  :file-id="selectedFileForDetails.id"
                  :alt="selectedFileForDetails.original_name"
                  width="w-full"
                  height="h-full"
                  object-fit="contain"
                />
                <div
                  v-else
                  class="flex flex-col items-center justify-center p-6"
                >
                  <component
                    :is="getFileIcon(selectedFileForDetails.mime_type)"
                    class="w-20 h-20 text-gray-400 mb-2"
                  />
                  <p class="text-sm text-gray-500">
                    {{ getFileExtension(selectedFileForDetails.original_name) }}
                  </p>
                </div>
              </div>

              <!-- Details -->
              <div class="space-y-3">
                <div>
                  <h3
                    class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1"
                  >
                    Назва файлу
                  </h3>
                  <p class="text-sm text-gray-900 dark:text-white break-words">
                    {{ selectedFileForDetails.original_name }}
                  </p>
                </div>

                <div>
                  <h3
                    class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1"
                  >
                    Розмір
                  </h3>
                  <p class="text-sm text-gray-900 dark:text-white">
                    {{ formatFileSize(selectedFileForDetails.size) }}
                  </p>
                </div>

                <div>
                  <h3
                    class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1"
                  >
                    Тип
                  </h3>
                  <p class="text-sm text-gray-900 dark:text-white">
                    {{ selectedFileForDetails.mime_type }}
                  </p>
                </div>

                <div v-if="selectedFileForDetails.user">
                  <h3
                    class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1"
                  >
                    Завантажив
                  </h3>
                  <div class="flex items-center gap-2">
                    <VAvatar
                      :name="selectedFileForDetails.user.name"
                      :file-id="selectedFileForDetails.user.avatar_file_id"
                      size="xs"
                      shape="circle"
                    />
                    <p class="text-sm text-gray-900 dark:text-white">
                      {{ selectedFileForDetails.user.name }}
                    </p>
                  </div>
                </div>

                <div>
                  <h3
                    class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1"
                  >
                    Дата завантаження
                  </h3>
                  <p class="text-sm text-gray-900 dark:text-white">
                    {{ formatDate(selectedFileForDetails.created_at) }}
                  </p>
                </div>
              </div>

              <!-- Actions -->
              <div class="pt-2 space-y-2">
                <UButton
                  v-if="!isFileSelected(selectedFileForDetails.id)"
                  color="primary"
                  block
                  @click="toggleFileSelection(selectedFileForDetails)"
                  :disabled="selectedFiles.length >= maxFiles"
                >
                  <template #leading>
                    <Check class="w-4 h-4" />
                  </template>
                  Вибрати файл
                </UButton>
                <UButton
                  v-else
                  color="neutral"
                  variant="outline"
                  block
                  @click="toggleFileSelection(selectedFileForDetails)"
                >
                  <template #leading>
                    <X class="w-4 h-4" />
                  </template>
                  Скасувати вибір
                </UButton>

                <UButton
                  color="error"
                  variant="outline"
                  block
                  @click="openDeleteModal(selectedFileForDetails)"
                >
                  <template #leading>
                    <Trash2 class="w-4 h-4" />
                  </template>
                  Видалити файл
                </UButton>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <VFileDeleteModal
        v-model:is-open="isDeleteModalOpen"
        :file="fileToDelete"
        @refresh="handleFileDeleted"
      />
    </template>

    <template #footer>
      <div class="flex items-center justify-between gap-4 w-full">
        <div class="flex-1 min-w-0">
          <VPagination
            v-if="meta"
            :meta="meta"
            @update:page="(page) => (filters.page = page)"
            @update:per-page="(perPage) => (filters.per_page = perPage)"
          />
        </div>
        <div class="flex items-center gap-4 shrink-0">
          <div class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">
            <span v-if="selectedFiles.length > 0">
              Вибрано: {{ selectedFiles.length }} з {{ maxFiles }}
            </span>
          </div>
          <div class="flex gap-2">
            <UButton variant="outline" color="neutral" @click="closeModal">
              <template #leading>
                <Ban class="w-4 h-4" />
              </template>
              Скасувати
            </UButton>
            <UButton
              color="primary"
              :disabled="selectedFiles.length === 0"
              @click="confirmSelection"
            >
              <template #leading>
                <Send class="w-4 h-4" />
              </template>
              Підтвердити{{
                selectedFiles.length > 1 ? ` (${selectedFiles.length})` : ""
              }}
            </UButton>
          </div>
        </div>
      </div>
    </template>
  </UModal>
</template>

<script setup lang="ts">
import {
  Upload,
  Loader2,
  FolderOpen,
  Image as ImageIcon,
  Video,
  Music,
  FileText,
  FileArchive,
  File as FileIcon,
  Search,
  X,
  CircleX,
  Filter,
  User,
  Check,
  Info,
  Trash2,
  Send,
  Ban,
} from "lucide-vue-next";
import type { Component } from "vue";
import type { IFile, IFileFilters } from "~/models/files";
import VFileDeleteModal from "@/components/files/modals/VFileDeleteModal.vue";
import VPagination from "@/components/common/VPagination.vue";
import VAvatar from "@/components/common/VAvatar.vue";
import VSecureImage from "~/components/common/VSecureImage.vue";

interface IProps {
  fileType?: "image" | "video" | "audio" | "pdf" | "archive" | "other";
  maxFiles?: number;
}

interface IEmits {
  (e: "select", files: IFile[]): void;
}

const props = withDefaults(defineProps<IProps>(), {
  maxFiles: 15,
});

const emits = defineEmits<IEmits>();

const { getFiles, uploadFile: uploadFileService } = useFiles();
const toast = useToast();

const isOpen = defineModel<boolean>("isOpen");

// Selected files
const selectedFiles = ref<IFile[]>([]);
const selectedFileForDetails = ref<IFile | null>(null);
const isDragging = ref(false);
const dragCounter = ref(0);
const isUploading = ref(false);
const isDeleteModalOpen = ref(false);
const fileToDelete = ref<IFile | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

// Filters - internal state for USelectMenu
const selectedTypesObjects = ref<string[]>([]);

// Actual filters for API
const filters = ref({
  search: "",
  user_search: "",
  types: [] as string[],
  page: 1,
  per_page: 15,
});

const fileTypeOptions = [
  { label: "Зображення", value: "image" },
  { label: "Відео", value: "video" },
  { label: "Аудіо", value: "audio" },
  { label: "PDF", value: "pdf" },
  { label: "Архіви", value: "archive" },
  { label: "Інше", value: "other" },
];

// Initialize filters with fileType prop
const initializeFilters = () => {
  if (props.fileType) {
    filters.value.types = [props.fileType];
    selectedTypesObjects.value = [props.fileType];
  }
};

// Watch for modal opening
watch(isOpen, (newValue) => {
  if (newValue) {
    initializeFilters();
    // Trigger refresh to apply fileType filter
    nextTick(() => {
      refreshFiles();
    });
  }
});

// Watch selectedTypesObjects and update filters.types (only if fileType is not set)
watch(
  selectedTypesObjects,
  (newVal) => {
    if (!props.fileType) {
      filters.value.types = newVal;
      refreshFiles();
    }
  },
  { deep: true }
);

// Use pagination list composable
const {
  data: files,
  meta,
  pending: isLoading,
  hasActiveFilters,
  clearFilters,
  refresh: refreshFiles,
} = await usePaginationList<typeof filters.value, IFile>({
  key: "file-picker-list",
  filters,
  fetchFunction: (filters?: IFileFilters) => getFiles(filters),
  debounceFields: ["search", "user_search"],
});

// Check if there are non-fileType filters active
const hasNonFileTypeFilters = computed(() => {
  const hasSearch = filters.value.search.trim().length > 0;
  const hasUserSearch = filters.value.user_search.trim().length > 0;
  const hasNonPropTypes = props.fileType
    ? selectedTypesObjects.value.some((type) => type !== props.fileType)
    : selectedTypesObjects.value.length > 0;

  return hasSearch || hasUserSearch || hasNonPropTypes;
});

// Filter methods
const resetFilters = () => {
  // Keep fileType filter if it's set via prop
  if (props.fileType) {
    selectedTypesObjects.value = [props.fileType];
    filters.value.types = [props.fileType];
  } else {
    selectedTypesObjects.value = [];
    filters.value.types = [];
  }
  filters.value.search = "";
  filters.value.user_search = "";
  clearFilters();
};

// File selection
const isFileSelected = (fileId: number): boolean => {
  return selectedFiles.value.some((f) => f.id === fileId);
};

const toggleFileSelection = (file: IFile) => {
  const index = selectedFiles.value.findIndex((f) => f.id === file.id);

  if (index > -1) {
    // Deselect
    selectedFiles.value.splice(index, 1);
  } else {
    // Select
    if (selectedFiles.value.length >= props.maxFiles) {
      toast.add({
        title: "Помилка",
        description: `Ви можете вибрати максимум ${props.maxFiles} ${
          props.maxFiles === 1 ? "файл" : "файлів"
        }`,
        color: "error",
      });
      return;
    }
    selectedFiles.value.push(file);
  }
};

const selectFileForDetails = (file: IFile) => {
  selectedFileForDetails.value = file;
};

const confirmSelection = () => {
  if (selectedFiles.value.length === 0) return;
  emits("select", selectedFiles.value);
  closeModal();
};

const closeModal = () => {
  selectedFiles.value = [];
  selectedFileForDetails.value = null;
  isDragging.value = false;
  dragCounter.value = 0;
  isOpen.value = false;
};

// File deletion
const openDeleteModal = (file: IFile) => {
  fileToDelete.value = file;
  isDeleteModalOpen.value = true;
};

const handleFileDeleted = async () => {
  // Remove from selected files if it was selected
  if (fileToDelete.value) {
    const selectedIndex = selectedFiles.value.findIndex(
      (f) => f.id === fileToDelete.value!.id
    );
    if (selectedIndex > -1) {
      selectedFiles.value.splice(selectedIndex, 1);
    }

    // Clear details if this file was selected for details
    if (selectedFileForDetails.value?.id === fileToDelete.value.id) {
      selectedFileForDetails.value = null;
    }
  }

  // Refresh files list
  await refreshFiles();

  // Clear fileToDelete
  fileToDelete.value = null;
};

// File upload
const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleFileSelect = async (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files) {
    await uploadFiles(Array.from(target.files));
    // Clear input
    if (fileInput.value) {
      fileInput.value.value = "";
    }
  }
};

const handleDragEnter = (event: DragEvent) => {
  event.preventDefault();
  dragCounter.value++;
  isDragging.value = true;
};

const handleDragLeave = (event: DragEvent) => {
  event.preventDefault();
  dragCounter.value--;
  if (dragCounter.value === 0) {
    isDragging.value = false;
  }
};

const handleDrop = async (event: DragEvent) => {
  event.preventDefault();
  dragCounter.value = 0;
  isDragging.value = false;
  if (event.dataTransfer?.files) {
    await uploadFiles(Array.from(event.dataTransfer.files));
  }
};

const uploadFiles = async (filesToUpload: File[]) => {
  const remainingSlots = props.maxFiles - selectedFiles.value.length;

  if (remainingSlots <= 0) {
    toast.add({
      title: "Помилка",
      description: `Ви вже вибрали максимальну кількість файлів (${props.maxFiles})`,
      color: "error",
    });
    return;
  }

  const filesToProcess = filesToUpload.slice(0, remainingSlots);

  if (filesToUpload.length > remainingSlots) {
    toast.add({
      title: "Попередження",
      description: `Буде завантажено ${filesToProcess.length} з ${filesToUpload.length} файлів. Максимум: ${props.maxFiles}`,
      color: "warning",
    });
  }

  isUploading.value = true;

  try {
    const uploadedFiles: IFile[] = [];

    for (const file of filesToProcess) {
      const uploadedFile = await uploadFileService({ file });
      uploadedFiles.push(uploadedFile);
    }

    // Refresh files list
    await refreshFiles();

    // Auto-select uploaded files
    for (const file of uploadedFiles) {
      if (selectedFiles.value.length < props.maxFiles) {
        selectedFiles.value.push(file);
      }
    }

    // Select last uploaded file for details
    if (uploadedFiles.length > 0) {
      selectedFileForDetails.value = uploadedFiles[uploadedFiles.length - 1];
    }

    toast.add({
      title: "Успішно",
      description: `${
        uploadedFiles.length > 1 ? "Файли" : "Файл"
      } успішно завантажено та вибрано`,
    });
  } catch (error: any) {
    console.error("Error uploading files:", error);
    toast.add({
      title: "Помилка",
      description: error?.data?.message || "Не вдалося завантажити файли",
      color: "error",
    });
  } finally {
    isUploading.value = false;
  }
};

const getAcceptAttribute = (type: string): string => {
  const acceptMap: Record<string, string> = {
    image: "image/*",
    video: "video/*",
    audio: "audio/*",
    pdf: "application/pdf",
    archive: ".zip,.rar,.7z,.tar,.gz",
  };
  return acceptMap[type] || "*";
};

// Helper functions
const isImage = (mimeType: string): boolean => {
  return mimeType.startsWith("image/");
};

const getFileIcon = (mimeType: string): Component => {
  if (mimeType.startsWith("image/")) return ImageIcon;
  if (mimeType.startsWith("video/")) return Video;
  if (mimeType.startsWith("audio/")) return Music;
  if (mimeType.includes("pdf")) return FileText;
  if (mimeType.includes("zip") || mimeType.includes("archive"))
    return FileArchive;
  return FileIcon;
};

const getFileExtension = (filename: string): string => {
  const parts = filename.split(".");
  return parts.length > 1 ? parts[parts.length - 1].toUpperCase() : "FILE";
};

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return "0 Bytes";
  const k = 1024;
  const sizes = ["Bytes", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  const size = sizes[i] || "Bytes";
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + " " + size;
};

const formatDate = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleDateString("uk-UA", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};
</script>
