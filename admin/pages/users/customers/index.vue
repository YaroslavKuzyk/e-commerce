<template>
  <VSidebarContent title="Покупці">
    <template #toolbar>
      <div class="flex items-center justify-between w-full gap-2">
        <div class="flex items-center gap-2 w-full">
          <UInput
            v-model="filters.search"
            placeholder="Пошук за ім'ям або email"
            icon="i-lucide-search"
            class="w-[250px]"
          />
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
          <HasPermissions :required-permissions="['Create Customer']">
            <UButton @click="openCreateModal">Додати покупця</UButton>
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
            <div class="flex items-center gap-2">
              <HasPermissions :required-permissions="['Update Customer']">
                <UButton
                  size="sm"
                  variant="ghost"
                  icon="i-lucide-pencil"
                  @click="openEditModal(row.original)"
                />
              </HasPermissions>
              <HasPermissions :required-permissions="['Delete Customer']">
                <UButton
                  size="sm"
                  variant="ghost"
                  color="error"
                  icon="i-lucide-trash-2"
                  @click="openDeleteModal(row.original)"
                />
              </HasPermissions>
            </div>
          </template>
        </UTable>

        <div
          v-if="!customersData || customersData.length === 0"
          class="text-center py-8 text-gray-500"
        >
          <div class="i-lucide-users text-4xl mx-auto mb-2 opacity-50"></div>
          <p>Покупців не знайдено</p>
        </div>
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
  </VSidebarContent>
</template>

<script setup lang="ts">
import HasPermissions from "~/components/common/VHasPermissions.vue";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VCustomerCreateModal from "~/components/customers/modals/VCustomerCreateModal.vue";
import VCustomerUpdateModal from "~/components/customers/modals/VCustomerUpdateModal.vue";
import VCustomerDeleteModal from "~/components/customers/modals/VCustomerDeleteModal.vue";
import type { ICustomer, ICustomerFilters } from "~/models/customers";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Read Customers"],
});

const customerStore = useCustomerStore();

const filters = ref({
  search: "",
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
  data: customersData,
  pending,
  hasActiveFilters,
  clearFilters,
  refresh: refreshCustomers,
  fetchData,
} = usePaginationList<typeof filters.value, ICustomer>({
  filters,
  fetchMethod: (filters?: ICustomerFilters) =>
    customerStore.fetchCustomers(filters),
  debounceFields: ["search"],
});

await fetchData();

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
