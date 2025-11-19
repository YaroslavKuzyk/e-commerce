<template>
  <VSidebarContent title="Файли">
    <template #toolbar>
      <div class="flex items-center justify-between w-full gap-2">
        <div class="flex items-center gap-2 w-full">
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
            v-if="hasActiveFilters"
            variant="ghost"
            @click="resetFilters"
          >
            <template #leading>
              <X class="w-5 h-5" />
            </template>
            Очистити фільтри
          </UButton>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <UButton
            v-if="selectedFileIds.length > 0"
            color="error"
            variant="outline"
            @click="openBulkDeleteModal"
          >
            <template #leading>
              <Trash2 class="w-4 h-4" />
            </template>
            Видалити ({{ selectedFileIds.length }})
          </UButton>
          <UButton @click="openUploadModal">
            <template #leading>
              <Upload class="w-4 h-4" />
            </template>
            Завантажити файл
          </UButton>
        </div>
      </div>
    </template>

    <div>
      <!-- Upload Modal -->
      <UploadFileModal
        v-model:is-open="isUploadModalOpen"
        @refresh="loadFiles"
      />

      <!-- Delete Modal -->
      <VFileDeleteModal
        v-model:is-open="isDeleteModalOpen"
        :file="fileToDelete"
        @refresh="loadFiles"
      />

      <!-- Bulk Delete Modal -->
      <VFileBulkDeleteModal
        v-model:is-open="isBulkDeleteModalOpen"
        :file-ids="selectedFileIds"
        @refresh="handleBulkDeleteRefresh"
      />

      <!-- Files Grid -->
      <div v-if="isLoading" class="flex items-center justify-center py-12">
        <Loader2 class="w-8 h-8 animate-spin text-gray-400" />
      </div>

      <div
        v-else-if="!files || (files.length === 0 && !hasActiveFilters)"
        class="text-center py-12"
      >
        <FolderOpen
          class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-700"
        />
        <p class="text-gray-500 dark:text-gray-400 mb-4">Файлів поки немає</p>
        <UButton color="primary" @click="openUploadModal">
          <template #leading>
            <Upload class="w-4 h-4" />
          </template>
          Завантажити перший файл
        </UButton>
      </div>

      <div
        v-else-if="!files || (files.length === 0 && hasActiveFilters)"
        class="text-center py-12"
      >
        <Filter
          class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-700"
        />
        <p class="text-gray-500 dark:text-gray-400 mb-4">
          За заданими фільтрами файлів не знайдено
        </p>
        <UButton color="neutral" variant="outline" @click="resetFilters">
          Очистити фільтри
        </UButton>
      </div>

      <div
        v-else-if="files && files.length > 0"
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
      >
        <div
          v-for="file in files"
          :key="file.id"
          class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow relative"
        >
          <!-- Checkbox -->
          <div class="absolute top-2 left-2 z-10">
            <UCheckbox
              :model-value="selectedFileIds.includes(file.id)"
              @update:model-value="toggleFileSelection(file.id)"
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
            <div v-else class="flex flex-col items-center justify-center p-6">
              <component
                :is="getFileIcon(file.mime_type)"
                class="w-16 h-16 text-gray-400 mb-2"
              />
              <p class="text-xs text-gray-500 text-center truncate w-full px-2">
                {{ getFileExtension(file.original_name) }}
              </p>
            </div>
          </div>

          <!-- File Info -->
          <div class="p-4">
            <h3
              class="text-sm font-medium text-gray-900 dark:text-white truncate mb-1"
            >
              {{ file.original_name }}
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
              {{ formatFileSize(file.size) }}
            </p>

            <div class="flex items-center gap-2 mb-3">
              <VAvatar
                v-if="file.user"
                :name="file.user.name"
                :file-id="file.user.avatar_file_id"
                size="xs"
                shape="circle"
              />
              <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-600 dark:text-gray-300 truncate">
                  {{ file.user?.name || "Невідомий" }}
                </p>
                <p class="text-xs text-gray-500">
                  {{ formatDate(file.created_at) }}
                </p>
              </div>
            </div>

            <div class="flex gap-2">
              <UButton
                color="neutral"
                variant="outline"
                size="sm"
                class="flex-1"
                @click="downloadFile(file)"
              >
                <template #leading>
                  <Download class="w-4 h-4" />
                </template>
                Завантажити
              </UButton>
              <UButton
                color="error"
                variant="outline"
                size="sm"
                @click="openDeleteModal(file)"
              >
                <Trash2 class="w-4 h-4" />
              </UButton>
            </div>
          </div>
        </div>
      </div>
    </div>

    <template #pagination>
      <VPagination
        :meta="meta"
        @update:page="(page) => { console.log('Files page: update:page', page); filters.page = page; }"
        @update:per-page="(perPage) => { console.log('Files page: update:per-page', perPage); filters.per_page = perPage; }"
      />
    </template>
  </VSidebarContent>
</template>

<script setup lang="ts">
import {
  Upload,
  Loader2,
  FolderOpen,
  Download,
  Trash2,
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
} from "lucide-vue-next";
import type { Component } from "vue";
import type { IFile, IFileFilters } from "~/models/files";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import UploadFileModal from "~/components/files/modals/UploadFileModal.vue";
import VFileDeleteModal from "~/components/files/modals/VFileDeleteModal.vue";
import VFileBulkDeleteModal from "~/components/files/modals/VFileBulkDeleteModal.vue";
import VPagination from "~/components/common/VPagination.vue";

definePageMeta({
  middleware: ["sanctum:auth"],
});

const { getFiles, getFileBlob } = useFiles();
const toast = useToast();

const isUploadModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isBulkDeleteModalOpen = ref(false);
const fileToDelete = ref<IFile | null>(null);
const selectedFileIds = ref<number[]>([]);

// Filters - internal state for USelectMenu (uses strings directly)
const selectedTypesObjects = ref<string[]>([]);

// Actual filters for API (uses strings)
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

// Watch selectedTypesObjects and update filters.types
watch(
  selectedTypesObjects,
  (newVal) => {
    filters.value.types = newVal;
    // Force refresh after filter update
    refreshFiles();
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
  key: "files-list",
  filters,
  fetchFunction: (filters?: IFileFilters) => getFiles(filters),
  debounceFields: ["search", "user_search"],
});

const getFileType = (mimeType: string): string => {
  if (mimeType.startsWith("image/")) return "image";
  if (mimeType.startsWith("video/")) return "video";
  if (mimeType.startsWith("audio/")) return "audio";
  if (mimeType.includes("pdf")) return "pdf";
  if (mimeType.includes("zip") || mimeType.includes("archive"))
    return "archive";
  return "other";
};

// Filter methods
const resetFilters = () => {
  selectedTypesObjects.value = [];
  clearFilters();
};

const removeTypeFilter = (type: string) => {
  filters.value.types = filters.value.types.filter((t) => t !== type);
};

const openUploadModal = () => {
  isUploadModalOpen.value = true;
};

const openDeleteModal = (file: IFile) => {
  fileToDelete.value = file;
  isDeleteModalOpen.value = true;
};

const loadFiles = async () => {
  await refreshFiles();
};

const downloadFile = async (file: IFile) => {
  try {
    const blob = await getFileBlob(file.id);
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = file.original_name;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);

    toast.add({
      title: "Успішно",
      description: "Файл завантажується",
    });
  } catch (error: any) {
    console.error("Error downloading file:", error);
    toast.add({
      title: "Помилка",
      description: "Не вдалося завантажити файл",
      color: "error",
    });
  }
};

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
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + " " + sizes[i];
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

const toggleFileSelection = (fileId: number) => {
  const index = selectedFileIds.value.indexOf(fileId);
  if (index > -1) {
    selectedFileIds.value.splice(index, 1);
  } else {
    selectedFileIds.value.push(fileId);
  }
};

const openBulkDeleteModal = () => {
  isBulkDeleteModalOpen.value = true;
};

const handleBulkDeleteRefresh = () => {
  selectedFileIds.value = [];
  loadFiles();
};
</script>
