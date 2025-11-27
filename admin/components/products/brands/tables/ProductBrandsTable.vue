<template>
  <div class="mt-4">
    <UTable :data="brands" :columns="columns" :loading="loading">
      <template #name-cell="{ row }">
        <span class="font-medium">{{ row.original.name }}</span>
      </template>

      <template #slug-cell="{ row }">
        <div class="text-sm text-gray-500">{{ row.original.slug }}</div>
      </template>

      <template #status-cell="{ row }">
        <UBadge
          :color="row.original.status === 'published' ? 'success' : 'neutral'"
          variant="subtle"
        >
          {{ row.original.status === 'published' ? 'Опубліковано' : 'Чернетка' }}
        </UBadge>
      </template>

      <template #logo_file_id-cell="{ row }">
        <div v-if="row.original.logo_file_id" class="flex items-center justify-center">
          <VSecureImage
            :file-id="row.original.logo_file_id"
            :alt="row.original.name"
            width="w-10"
            height="h-10"
            object-fit="cover"
            class="rounded"
          />
        </div>
        <span v-else class="text-gray-400 text-sm">Немає</span>
      </template>

      <template #actions-cell="{ row }">
        <div class="flex items-center justify-end gap-2">
          <HasPermissions :required-permissions="['Update Product Brand']">
            <UButton
              size="sm"
              variant="ghost"
              icon="i-heroicons-pencil"
              @click="emits('edit', row.original)"
            />
          </HasPermissions>
          <HasPermissions :required-permissions="['Delete Product Brand']">
            <UButton
              size="sm"
              variant="ghost"
              icon="i-heroicons-trash"
              color="error"
              @click="emits('delete', row.original)"
            />
          </HasPermissions>
        </div>
      </template>
    </UTable>
  </div>
</template>

<script setup lang="ts">
import VSecureImage from "~/components/common/VSecureImage.vue";
import HasPermissions from "~/components/common/VHasPermissions.vue";
import type { ProductBrand } from "~/models/productBrand";

interface Props {
  brands: ProductBrand[];
  loading?: boolean;
}

interface Emits {
  (e: "edit", brand: ProductBrand): void;
  (e: "delete", brand: ProductBrand): void;
}

const props = defineProps<Props>();
const emits = defineEmits<Emits>();

const columns = [
  {
    id: "name",
    header: "Назва",
  },
  {
    id: "slug",
    header: "Slug",
    meta: { class: { th: "w-[200px]" } },
  },
  {
    id: "status",
    header: "Статус",
    meta: { class: { th: "w-[120px]" } },
  },
  {
    id: "logo_file_id",
    header: "Логотип",
    meta: { class: { th: "w-[100px] text-center" } },
  },
  {
    id: "actions",
    header: "Дії",
    meta: { class: { th: "w-[100px] text-right", td: "text-right" } },
  },
];
</script>
