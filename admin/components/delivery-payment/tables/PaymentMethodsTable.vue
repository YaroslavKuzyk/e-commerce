<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold">Методи оплати</h2>
    </div>

    <UTable :data="paymentMethods" :columns="columns" :loading="loading">
      <template #name-cell="{ row }">
        <div>
          <div class="font-medium">{{ row.original.name }}</div>
          <div v-if="row.original.name_uk" class="text-sm text-gray-500">
            {{ row.original.name_uk }}
          </div>
        </div>
      </template>

      <template #type-cell="{ row }">
        <UBadge
          :color="row.original.type === 'online' ? 'primary' : 'success'"
          variant="subtle"
        >
          {{ row.original.type === "online" ? "Онлайн" : "При отриманні" }}
        </UBadge>
      </template>

      <template #provider-cell="{ row }">
        <span v-if="row.original.provider" class="capitalize">{{
          row.original.provider
        }}</span>
        <span v-else class="text-gray-400">—</span>
      </template>

      <template #is_active-cell="{ row }">
        <UBadge
          :color="row.original.is_active ? 'success' : 'error'"
          variant="subtle"
        >
          {{ row.original.is_active ? "Активний" : "Неактивний" }}
        </UBadge>
      </template>

      <template #actions-cell="{ row }">
        <div class="flex items-center justify-end gap-2">
          <HasPermissions :required-permissions="['Update Payment Method']">
            <USwitch
              :model-value="row.original.is_active"
              @update:model-value="emits('toggle-active', row.original.id)"
            />
          </HasPermissions>
        </div>
      </template>
    </UTable>
  </div>
</template>

<script setup lang="ts">
import HasPermissions from "~/components/common/VHasPermissions.vue";
import type { PaymentMethod } from "~/models/paymentMethod";

interface IProps {
  paymentMethods: PaymentMethod[];
  loading?: boolean;
}

interface IEmits {
  (e: "toggle-active", id: number): void;
}

defineProps<IProps>();
const emits = defineEmits<IEmits>();

const columns = [
  { header: "ID", accessorKey: "id" },
  { id: "name", header: "Назва" },
  { header: "Код", accessorKey: "code" },
  { id: "type", header: "Тип" },
  { id: "provider", header: "Провайдер" },
  { id: "is_active", header: "Статус" },
  {
    id: "actions",
    header: "Дії",
    meta: { class: { th: "w-[120px] text-right", td: "text-right" } },
  },
];
</script>
