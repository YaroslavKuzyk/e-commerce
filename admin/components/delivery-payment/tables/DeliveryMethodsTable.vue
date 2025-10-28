<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold">Методи доставки</h2>
    </div>

    <UTable :data="deliveryMethods" :columns="columns" :loading="loading">
      <template #name-cell="{ row }">
        <div>
          <div class="font-medium">{{ row.original.name }}</div>
          <div v-if="row.original.name_uk" class="text-sm text-gray-500">
            {{ row.original.name_uk }}
          </div>
        </div>
      </template>

      <template #has_api-cell="{ row }">
        <UBadge
          :color="row.original.has_api ? 'success' : 'neutral'"
          variant="subtle"
        >
          {{ row.original.has_api ? "Так" : "Ні" }}
        </UBadge>
      </template>

      <template #is_active-cell="{ row }">
        <UBadge
          :color="row.original.is_active ? 'success' : 'error'"
          variant="subtle"
        >
          {{ row.original.is_active ? "Активний" : "Неактивний" }}
        </UBadge>
      </template>

      <template #payment_methods-cell="{ row }">
        <div
          v-if="
            row.original.payment_methods &&
            row.original.payment_methods.length > 0
          "
          class="flex flex-wrap gap-1"
        >
          <UBadge
            v-for="pm in row.original.payment_methods"
            :key="pm.id"
            :color="pm.pivot_is_active ? 'primary' : 'neutral'"
            variant="subtle"
          >
            {{ pm.name_uk || pm.name }}
          </UBadge>
        </div>
        <span v-else class="text-gray-400 text-sm">Немає методів</span>
      </template>

      <template #actions-cell="{ row }">
        <div class="flex items-center justify-end gap-2">
          <HasPermissions :required-permissions="['Update Delivery Method']">
            <USwitch
              :model-value="row.original.is_active"
              @update:model-value="emits('toggle-active', row.original.id)"
            />

            <UButton
              size="sm"
              variant="ghost"
              icon="i-heroicons-pencil"
              @click="emits('edit', row.original)"
            />
            <UButton
              size="sm"
              variant="ghost"
              icon="i-heroicons-credit-card"
              @click="emits('manage-payment-methods', row.original)"
            />
          </HasPermissions>
        </div>
      </template>
    </UTable>
  </div>
</template>

<script setup lang="ts">
import HasPermissions from "~/components/common/VHasPermissions.vue";
import type { DeliveryMethod } from "~/models/deliveryMethod";

interface IProps {
  deliveryMethods: DeliveryMethod[];
  loading?: boolean;
}

interface IEmits {
  (e: "toggle-active", id: number): void;
  (e: "edit", deliveryMethod: DeliveryMethod): void;
  (e: "manage-payment-methods", deliveryMethod: DeliveryMethod): void;
}

defineProps<IProps>();
const emits = defineEmits<IEmits>();

const columns = [
  { header: "ID", accessorKey: "id" },
  { id: "name", header: "Назва" },
  { header: "Код", accessorKey: "code" },
  { id: "has_api", header: "API" },
  { id: "payment_methods", header: "Методи оплати" },
  { id: "is_active", header: "Статус" },
  {
    id: "actions",
    header: "Дії",
    meta: { class: { th: "w-[250px] text-right", td: "text-right" } },
  },
];
</script>
