<template>
  <VSidebarContent :title="$t('callbackRequests.title')">
    <template #toolbar>
      <div class="flex items-center justify-between w-full gap-2">
        <div class="flex items-center gap-2 w-full flex-wrap">
          <UInput
            v-model="filters.search"
            :placeholder="$t('callbackRequests.searchPlaceholder')"
            class="w-[200px]"
          >
            <template #leading>
              <Search class="w-5 h-5" />
            </template>
          </UInput>
          <USelectMenu
            v-model="filters.status"
            :items="statusOptions"
            :placeholder="$t('common.status')"
            value-key="value"
            label-key="label"
            class="w-[150px]"
          >
            <template #trailing>
              <UButton
                v-if="filters.status"
                size="sm"
                variant="link"
                :aria-label="$t('common.clear')"
                color="neutral"
                @click.stop="filters.status = null"
              >
                <CircleX class="w-4 h-4" />
              </UButton>
            </template>
          </USelectMenu>
          <UButton
            v-if="hasActiveFilters"
            variant="ghost"
            @click="clearFilters"
          >
            <template #leading>
              <X class="w-5 h-5" />
            </template>
            {{ $t("common.clearFilters") }}
          </UButton>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <UBadge variant="subtle" color="info">
            {{ $t("callbackRequests.stats.new") }}: {{ stats?.new || 0 }}
          </UBadge>
          <UBadge variant="subtle" color="warning">
            {{ $t("callbackRequests.stats.inProgress") }}:
            {{ stats?.in_progress || 0 }}
          </UBadge>
          <UBadge variant="subtle" color="success">
            {{ $t("callbackRequests.stats.completed") }}:
            {{ stats?.completed || 0 }}
          </UBadge>
        </div>
      </div>
    </template>

    <HasPermissions :required-permissions="['Read Callback Requests']">
      <!-- View Modal -->
      <UModal
        v-model:open="isViewModalOpen"
        :title="$t('callbackRequests.viewRequest')"
      >
        <template #body>
          <div v-if="selectedRequest" class="space-y-4">
            <div>
              <p class="text-sm text-gray-500">
                {{ $t("callbackRequests.phone") }}
              </p>
              <p class="font-medium">{{ selectedRequest.phone }}</p>
            </div>

            <div v-if="selectedRequest.name">
              <p class="text-sm text-gray-500">
                {{ $t("callbackRequests.name") }}
              </p>
              <p class="font-medium">{{ selectedRequest.name }}</p>
            </div>

            <div v-if="selectedRequest.comment">
              <p class="text-sm text-gray-500">
                {{ $t("callbackRequests.comment") }}
              </p>
              <p class="whitespace-pre-wrap">{{ selectedRequest.comment }}</p>
            </div>

            <div>
              <p class="text-sm text-gray-500">{{ $t("common.status") }}</p>
              <UBadge
                :color="getStatusColor(selectedRequest.status)"
                variant="subtle"
              >
                {{
                  $t(`callbackRequests.status.${selectedRequest.status}`)
                }}
              </UBadge>
            </div>

            <div>
              <p class="text-sm text-gray-500">
                {{ $t("callbackRequests.createdAt") }}
              </p>
              <p>{{ formatDate(selectedRequest.created_at) }}</p>
            </div>

            <USeparator />

            <HasPermissions :required-permissions="['Update Callback Request']">
              <div>
                <p class="text-sm text-gray-500 mb-2">
                  {{ $t("callbackRequests.changeStatus") }}
                </p>
                <div class="flex gap-2 flex-wrap">
                  <UButton
                    v-for="status in statusItems"
                    :key="status.value"
                    :variant="
                      selectedRequest.status === status.value
                        ? 'solid'
                        : 'outline'
                    "
                    :color="status.color"
                    size="sm"
                    @click="updateStatus(selectedRequest.id, status.value)"
                  >
                    {{ status.label }}
                  </UButton>
                </div>
              </div>
            </HasPermissions>

            <USeparator />

            <div class="flex justify-end gap-2">
              <UButton
                type="button"
                variant="outline"
                color="neutral"
                @click="isViewModalOpen = false"
              >
                <template #leading>
                  <Ban class="w-4 h-4" />
                </template>
                {{ $t("common.close") }}
              </UButton>
            </div>
          </div>
        </template>
      </UModal>

      <!-- Delete Modal -->
      <UModal
        v-model:open="isDeleteModalOpen"
        :title="$t('callbackRequests.deleteTitle')"
        :description="$t('callbackRequests.deleteWarning')"
      >
        <template #body>
          <div class="space-y-4">
            <div
              v-if="requestToDelete"
              class="p-4 bg-error-50 dark:bg-error-900/20 rounded-lg"
            >
              <p class="text-sm text-error-600 dark:text-error-400">
                {{ $t("callbackRequests.deleteConfirm") }}
              </p>
              <p class="font-semibold mt-2">{{ requestToDelete.phone }}</p>
              <p v-if="requestToDelete.name" class="text-sm text-gray-500 mt-1">
                {{ requestToDelete.name }}
              </p>
            </div>

            <USeparator />

            <div class="flex justify-end gap-2">
              <UButton
                type="button"
                variant="outline"
                color="neutral"
                @click="isDeleteModalOpen = false"
              >
                <template #leading>
                  <Ban class="w-4 h-4" />
                </template>
                {{ $t("common.cancel") }}
              </UButton>
              <UButton
                type="button"
                color="error"
                :loading="deleteLoading"
                @click="handleDeleteRequest"
              >
                <template #leading>
                  <Send class="w-4 h-4" />
                </template>
                {{ $t("common.confirm") }}
              </UButton>
            </div>
          </div>
        </template>
      </UModal>

      <UTable :data="requestsData || []" :columns="columns" :loading="pending">
        <template #phone-cell="{ row }">
          <p class="font-medium">{{ row.original.phone }}</p>
        </template>

        <template #name-cell="{ row }">
          <p v-if="row.original.name">{{ row.original.name }}</p>
          <span v-else class="text-gray-400">—</span>
        </template>

        <template #comment-cell="{ row }">
          <div class="max-w-[300px]">
            <p
              v-if="row.original.comment"
              class="text-sm text-gray-600 dark:text-gray-400 truncate"
            >
              {{ row.original.comment }}
            </p>
            <span v-else class="text-gray-400">—</span>
          </div>
        </template>

        <template #status-cell="{ row }">
          <UBadge :color="getStatusColor(row.original.status)" variant="subtle">
            {{ $t(`callbackRequests.status.${row.original.status}`) }}
          </UBadge>
        </template>

        <template #created_at-cell="{ row }">
          <span class="text-gray-600 dark:text-gray-400">
            {{ formatDate(row.original.created_at) }}
          </span>
        </template>

        <template #actions-cell="{ row }">
          <div class="flex items-center gap-1">
            <UButton
              size="sm"
              variant="ghost"
              icon="i-heroicons-eye"
              @click="handleView(row.original)"
            />
            <HasPermissions :required-permissions="['Update Callback Request']">
              <UButton
                v-if="row.original.status !== 'completed'"
                size="sm"
                variant="ghost"
                color="success"
                @click="updateStatus(row.original.id, 'completed')"
              >
                <Check class="w-4 h-4" />
              </UButton>
            </HasPermissions>
            <HasPermissions :required-permissions="['Delete Callback Request']">
              <UButton
                size="sm"
                variant="ghost"
                color="error"
                icon="i-heroicons-trash"
                @click="handleDelete(row.original)"
              />
            </HasPermissions>
          </div>
        </template>
      </UTable>
    </HasPermissions>

    <template #pagination>
      <VPagination
        :meta="meta"
        @update:page="(page) => (filters.page = page)"
        @update:per-page="(perPage) => (filters.per_page = perPage)"
      />
    </template>
  </VSidebarContent>
</template>

<script setup lang="ts">
import {
  Search,
  CircleX,
  X,
  Check,
  Ban,
  Send,
} from "lucide-vue-next";
import HasPermissions from "~/components/common/VHasPermissions.vue";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VPagination from "~/components/common/VPagination.vue";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Read Callback Requests"],
});

interface CallbackRequest {
  id: number;
  phone: string;
  name: string | null;
  comment: string | null;
  status: "new" | "in_progress" | "completed" | "cancelled";
  created_at: string;
  updated_at: string;
}

interface Stats {
  total: number;
  new: number;
  in_progress: number;
  completed: number;
  cancelled: number;
}

type CallbackStatus = "new" | "in_progress" | "completed" | "cancelled";

const { t } = useI18n();
const toast = useToast();
const client = useSanctumClient();

const isViewModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedRequest = ref<CallbackRequest | null>(null);
const requestToDelete = ref<CallbackRequest | null>(null);
const deleteLoading = ref(false);

const filters = ref({
  search: "",
  status: null as CallbackStatus | null,
  page: 1,
  per_page: 15,
});

const statusOptions = computed(() => [
  { label: t("callbackRequests.status.new"), value: "new" },
  { label: t("callbackRequests.status.in_progress"), value: "in_progress" },
  { label: t("callbackRequests.status.completed"), value: "completed" },
  { label: t("callbackRequests.status.cancelled"), value: "cancelled" },
]);

const statusItems = computed(() => [
  {
    value: "new",
    label: t("callbackRequests.status.new"),
    color: "info" as const,
  },
  {
    value: "in_progress",
    label: t("callbackRequests.status.in_progress"),
    color: "warning" as const,
  },
  {
    value: "completed",
    label: t("callbackRequests.status.completed"),
    color: "success" as const,
  },
  {
    value: "cancelled",
    label: t("callbackRequests.status.cancelled"),
    color: "error" as const,
  },
]);

const columns = computed(() => [
  {
    id: "phone",
    header: t("callbackRequests.phone"),
    meta: { class: { th: "w-[150px]" } },
  },
  {
    id: "name",
    header: t("callbackRequests.name"),
    meta: { class: { th: "w-[150px]" } },
  },
  { id: "comment", header: t("callbackRequests.comment") },
  {
    id: "status",
    header: t("common.status"),
    meta: { class: { th: "w-[120px]" } },
  },
  {
    id: "created_at",
    header: t("callbackRequests.createdAt"),
    meta: { class: { th: "w-[150px]" } },
  },
  { id: "actions", header: t("common.actions"), meta: { class: { th: "w-32 text-end", td: "text-end" } } },
]);

// Fetch stats
const { data: statsData, refresh: refreshStats } = await useAsyncData(
  "callback-requests-stats",
  () => client("/api/admin/callback-requests/stats")
);
const stats = computed(() => statsData.value?.data as Stats | null);

// Fetch list using usePaginationList pattern
const fetchCallbackRequests = async (filterParams?: typeof filters.value) => {
  const params: Record<string, string | number> = {
    page: filterParams?.page || 1,
    per_page: filterParams?.per_page || 15,
  };

  if (filterParams?.search) {
    params.search = filterParams.search;
  }

  if (filterParams?.status) {
    params.status = filterParams.status;
  }

  return client("/api/admin/callback-requests", { params });
};

const {
  data: requestsData,
  meta,
  pending,
  hasActiveFilters,
  clearFilters,
  refresh: internalRefresh,
} = await usePaginationList<typeof filters.value, CallbackRequest>({
  key: "callback-requests-list",
  filters,
  fetchFunction: fetchCallbackRequests,
  debounceFields: ["search"],
});

const getStatusColor = (status: CallbackStatus) => {
  switch (status) {
    case "new":
      return "info";
    case "in_progress":
      return "warning";
    case "completed":
      return "success";
    case "cancelled":
      return "error";
    default:
      return "neutral";
  }
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString("uk-UA", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const refreshRequests = async () => {
  await internalRefresh();
  await refreshStats();
};

const handleView = (request: CallbackRequest) => {
  selectedRequest.value = request;
  isViewModalOpen.value = true;
};

const updateStatus = async (id: number, status: string) => {
  try {
    await client(`/api/admin/callback-requests/${id}`, {
      method: "PUT",
      body: { status },
    });

    toast.add({
      title: t("common.success"),
      description: t("callbackRequests.updateSuccess"),
      color: "success",
    });

    if (selectedRequest.value?.id === id) {
      selectedRequest.value = {
        ...selectedRequest.value,
        status: status as CallbackStatus,
      };
    }

    await refreshRequests();
  } catch (error) {
    toast.add({
      title: t("common.error"),
      description: t("callbackRequests.updateError"),
      color: "error",
    });
  }
};

const handleDelete = (request: CallbackRequest) => {
  requestToDelete.value = request;
  isDeleteModalOpen.value = true;
};

const handleDeleteRequest = async () => {
  if (!requestToDelete.value) return;

  deleteLoading.value = true;
  try {
    await client(`/api/admin/callback-requests/${requestToDelete.value.id}`, {
      method: "DELETE",
    });

    toast.add({
      title: t("common.success"),
      description: t("callbackRequests.deleteSuccess"),
      color: "success",
    });

    isDeleteModalOpen.value = false;
    requestToDelete.value = null;
    await refreshRequests();
  } catch (error) {
    toast.add({
      title: t("common.error"),
      description: t("callbackRequests.deleteError"),
      color: "error",
    });
  } finally {
    deleteLoading.value = false;
  }
};
</script>
