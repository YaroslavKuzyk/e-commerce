<template>
  <VSidebarContent title="Адміністратори">
    <template #toolbar>
      <div class="flex items-center justify-between w-full gap-2">
        <div class="flex items-center gap-2 w-full">
          <UInput
            v-model="filters.search"
            placeholder="Пошук за ім'ям або email"
            icon="i-lucide-search"
            class="w-[200px]"
          />
          <USelectMenu
            v-model="filters.role"
            :items="rolesData || []"
            placeholder="Роль"
            value-key="id"
            label-key="name"
            class="w-[150px]"
          >
            <template #trailing>
              <UButton
                v-if="filters.role"
                size="sm"
                variant="link"
                icon="i-lucide-circle-x"
                aria-label="Очистити"
                @click.stop="filters.role = null"
                color="neutral"
              />
            </template>
          </USelectMenu>
          <USelectMenu
            v-model="filters.status"
            :items="statusOptions"
            placeholder="Статус"
            value-key="value"
            label-key="label"
            class="w-[150px]"
          >
            <template #trailing>
              <UButton
                v-if="filters.status"
                size="sm"
                variant="link"
                icon="i-lucide-circle-x"
                aria-label="Очистити"
                @click.stop="filters.status = null"
                color="neutral"
              />
            </template>
          </USelectMenu>
          <UButton
            v-if="hasActiveFilters"
            variant="ghost"
            @click="clearFilters"
            icon="i-lucide-x"
          >
            Очистити фільтри
          </UButton>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <HasPermissions :required-permissions="['Create Admin User']">
            <UButton @click="openCreateModal">Додати адміністратора</UButton>
          </HasPermissions>
        </div>
      </div>
    </template>

    <HasPermissions :required-permissions="['Read Admin Users']">
      <div class="mt-4">
        <UTable :data="adminsData" :columns="columns" :loading="pending">
          <template #status-cell="{ row }">
            <UBadge
              :color="row.original.status === 'active' ? 'success' : 'error'"
              variant="subtle"
            >
              {{ row.original.status === "active" ? "Активний" : "Неактивний" }}
            </UBadge>
          </template>

          <template #role-cell="{ row }">
            <span v-if="row.original.role">{{ row.original.role.name }}</span>
            <span v-else class="text-gray-400">Немає ролі</span>
          </template>

          <template #actions-cell="{ row }">
            <div class="flex items-center gap-2">
              <HasPermissions :required-permissions="['Update Admin User']">
                <UTooltip
                  v-if="isSuperAdmin(row.original)"
                  text="Неможливо редагувати SuperAdmin"
                  :popper="{ placement: 'left' }"
                >
                  <UButton
                    size="sm"
                    variant="ghost"
                    color="neutral"
                    icon="i-lucide-shield-check"
                    disabled
                  />
                </UTooltip>
                <UButton
                  v-else
                  size="sm"
                  variant="ghost"
                  icon="i-lucide-pencil"
                  @click="openEditModal(row.original)"
                />
              </HasPermissions>
              <HasPermissions :required-permissions="['Delete Admin User']">
                <UButton
                  v-if="!isSuperAdmin(row.original)"
                  size="sm"
                  variant="ghost"
                  color="error"
                  icon="i-lucide-trash-2"
                  @click="openDeleteModal(row.original)"
                />
                <UTooltip
                  v-else
                  text="Неможливо видалити SuperAdmin"
                  :popper="{ placement: 'left' }"
                >
                  <UButton
                    size="sm"
                    variant="ghost"
                    color="neutral"
                    icon="i-lucide-shield-check"
                    disabled
                  />
                </UTooltip>
              </HasPermissions>
            </div>
          </template>
        </UTable>

        <div
          v-if="!adminsData || adminsData.length === 0"
          class="text-center py-8 text-gray-500"
        >
          <div class="i-lucide-users text-4xl mx-auto mb-2 opacity-50"></div>
          <p>Адміністраторів не знайдено</p>
        </div>
      </div>
    </HasPermissions>

    <VAdminCreateModal
      v-model:is-open="isCreateModalOpen"
      @refresh="refreshAdmins"
    />
    <VAdminUpdateModal
      v-model:is-open="isUpdateModalOpen"
      :admin="selectedAdmin"
      @refresh="refreshAdmins"
    />
    <VAdminDeleteModal
      v-model:is-open="isDeleteModalOpen"
      :admin="selectedAdmin"
      @refresh="refreshAdmins"
    />
  </VSidebarContent>
</template>

<script setup lang="ts">
import HasPermissions from "~/components/common/VHasPermissions.vue";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VAdminCreateModal from "~/components/administrators/modals/VAdminCreateModal.vue";
import VAdminUpdateModal from "~/components/administrators/modals/VAdminUpdateModal.vue";
import VAdminDeleteModal from "~/components/administrators/modals/VAdminDeleteModal.vue";
import type { IAdmin, IAdminFilters } from "~/models/administrators";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Read Admin Users"],
});

const roleStore = useRoleStore();
const adminStore = useAdminStore();

const { data: rolesData } = await roleStore.fetchRoles();

const filters = ref({
  search: "",
  role: null as number | null,
  status: null as "active" | "inactive" | null,
});

const statusOptions = [
  { label: "Активний", value: "active" },
  { label: "Неактивний", value: "inactive" },
];

const columns = [
  {
    header: "ID",
    accessorKey: "id",
  },
  {
    header: "Ім'я",
    accessorKey: "name",
  },
  {
    header: "Email",
    accessorKey: "email",
  },
  {
    id: "status",
    header: "Статус",
  },
  {
    id: "role",
    header: "Роль",
  },
  {
    id: "actions",
    header: "Дії",
    meta: {
      class: {
        th: "w-[120px]",
      },
    },
  },
];

const {
  data: adminsData,
  pending,
  hasActiveFilters,
  clearFilters,
  refresh: refreshAdmins,
  fetchData,
} = usePaginationList<typeof filters.value, IAdmin>({
  filters,
  fetchMethod: (filters?: IAdminFilters) => adminStore.fetchAdmins(filters),
  debounceFields: ["search"],
});

await fetchData();

const isSuperAdmin = (admin: IAdmin): boolean => {
  return admin.role?.name === "SuperAdmin";
};

const isCreateModalOpen = ref(false);
const isUpdateModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedAdmin = ref<IAdmin | null>(null);

const openCreateModal = () => {
  isCreateModalOpen.value = true;
};

const openEditModal = (admin: IAdmin) => {
  selectedAdmin.value = admin;
  isUpdateModalOpen.value = true;
};

const openDeleteModal = (admin: IAdmin) => {
  selectedAdmin.value = admin;
  isDeleteModalOpen.value = true;
};
</script>
