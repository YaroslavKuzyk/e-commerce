<template>
  <VSidebarContent title="Покупці">
    <template #toolbar>
      <div class="flex items-center justify-between w-full gap-2">
        <div class="flex items-center gap-2 w-full">
          <UInput
            v-model="filters.search"
            placeholder="Пошук за ім'ям або email"
            class="w-[250px]"
          >
            <template #leading>
              <Search class="w-5 h-5" />
            </template>
          </UInput>
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
                aria-label="Очистити"
                @click.stop="filters.status = null"
                color="neutral"
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
            Очистити фільтри
          </UButton>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <HasPermissions :required-permissions="['Create Customer']">
            <UButton @click="openCreateModal">
              <template #leading>
                <Plus class="w-4 h-4" />
              </template>
              Додати покупця
            </UButton>
          </HasPermissions>
        </div>
      </div>
    </template>

    <HasPermissions :required-permissions="['Read Customers']">
      <div class="mt-4">
        <UTable :data="customersData" :columns="columns" :loading="pending">
          <template #status-cell="{ row }">
            <UBadge
              :color="row.original.status === 'active' ? 'success' : 'error'"
              variant="subtle"
            >
              {{ row.original.status === "active" ? "Активний" : "Неактивний" }}
            </UBadge>
          </template>

          <template #actions-cell="{ row }">
            <div class="flex items-center justify-end gap-2">
              <HasPermissions :required-permissions="['Update Customer']">
                <UButton
                  size="sm"
                  variant="ghost"
                  @click="openEditModal(row.original)"
                >
                  <Pencil class="w-4 h-4" />
                </UButton>
              </HasPermissions>
              <HasPermissions :required-permissions="['Delete Customer']">
                <UButton
                  size="sm"
                  variant="ghost"
                  color="error"
                  @click="openDeleteModal(row.original)"
                >
                  <Trash2 class="w-4 h-4" />
                </UButton>
              </HasPermissions>
            </div>
          </template>
        </UTable>
      </div>
    </HasPermissions>

    <VCustomerCreateModal
      v-model:is-open="isCreateModalOpen"
      @refresh="refreshCustomers"
    />
    <VCustomerUpdateModal
      v-model:is-open="isUpdateModalOpen"
      :customer="selectedCustomer"
      @refresh="refreshCustomers"
    />
    <VCustomerDeleteModal
      v-model:is-open="isDeleteModalOpen"
      :customer="selectedCustomer"
      @refresh="refreshCustomers"
    />

    <template #pagination>
      <VPagination
        :meta="meta"
        @update:page="(page) => filters.page = page"
        @update:per-page="(perPage) => filters.per_page = perPage"
      />
    </template>
  </VSidebarContent>
</template>

<script setup lang="ts">
import { Search, CircleX, X, Pencil, Trash2, Plus } from "lucide-vue-next";
import HasPermissions from "~/components/common/VHasPermissions.vue";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VCustomerCreateModal from "~/components/customers/modals/VCustomerCreateModal.vue";
import VCustomerUpdateModal from "~/components/customers/modals/VCustomerUpdateModal.vue";
import VCustomerDeleteModal from "~/components/customers/modals/VCustomerDeleteModal.vue";
import VPagination from "~/components/common/VPagination.vue";
import type { ICustomer, ICustomerFilters } from "~/models/customers";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Read Customers"],
});

const customerStore = useCustomerStore();

const filters = ref({
  search: "",
  status: null as "active" | "inactive" | null,
  page: 1,
  per_page: 15,
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
    id: "actions",
    header: "Дії",
    meta: {
      class: {
        th: "w-[120px] text-right",
        td: "text-right",
      },
    },
  },
];

const {
  data: customersData,
  meta,
  pending,
  hasActiveFilters,
  clearFilters,
  refresh: refreshCustomers,
} = await usePaginationList<typeof filters.value, ICustomer>({
  key: 'customers-list',
  filters,
  fetchFunction: (filters?: ICustomerFilters) => customerStore.fetchCustomersPromise(filters),
  debounceFields: ["search"],
});

const isCreateModalOpen = ref(false);
const isUpdateModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedCustomer = ref<ICustomer | null>(null);

const openCreateModal = () => {
  isCreateModalOpen.value = true;
};

const openEditModal = (customer: ICustomer) => {
  selectedCustomer.value = customer;
  isUpdateModalOpen.value = true;
};

const openDeleteModal = (customer: ICustomer) => {
  selectedCustomer.value = customer;
  isDeleteModalOpen.value = true;
};
</script>
