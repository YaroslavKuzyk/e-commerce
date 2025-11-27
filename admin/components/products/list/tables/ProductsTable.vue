<template>
  <div class="mt-4">
    <UTable :data="products" :columns="columns" :loading="loading">
      <template #main_image-cell="{ row }">
        <div v-if="row.original.main_image_file_id" class="flex items-center justify-center">
          <VSecureImage
            :file-id="row.original.main_image_file_id"
            :alt="row.original.name"
            width="w-12"
            height="h-12"
            object-fit="cover"
            class="rounded"
          />
        </div>
        <span v-else class="text-gray-400 text-sm">Немає</span>
      </template>

      <template #name-cell="{ row }">
        <div>
          <span class="font-medium">{{ row.original.name }}</span>
          <div class="text-sm text-gray-500">{{ row.original.slug }}</div>
        </div>
      </template>

      <template #category-cell="{ row }">
        <span v-if="row.original.category" class="text-sm">
          {{ row.original.category.name }}
        </span>
        <span v-else class="text-gray-400 text-sm">—</span>
      </template>

      <template #brand-cell="{ row }">
        <span v-if="row.original.brand" class="text-sm">
          {{ row.original.brand.name }}
        </span>
        <span v-else class="text-gray-400 text-sm">—</span>
      </template>

      <template #base_price-cell="{ row }">
        <span class="font-medium">{{ formatPrice(row.original.base_price) }}</span>
      </template>

      <template #status-cell="{ row }">
        <UBadge
          :color="row.original.status === 'published' ? 'success' : 'neutral'"
          variant="subtle"
        >
          {{ row.original.status === 'published' ? 'Опубліковано' : 'Чернетка' }}
        </UBadge>
      </template>

      <template #actions-cell="{ row }">
        <div class="flex items-center justify-end gap-2">
          <HasPermissions :required-permissions="['Update Product']">
            <UButton
              size="sm"
              variant="ghost"
              icon="i-heroicons-pencil"
              @click="emits('edit', row.original)"
            />
          </HasPermissions>
          <HasPermissions :required-permissions="['Delete Product']">
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
import type { Product } from "~/models/product";

interface Props {
  products: Product[];
  loading?: boolean;
}

interface Emits {
  (e: "edit", product: Product): void;
  (e: "delete", product: Product): void;
}

const props = defineProps<Props>();
const emits = defineEmits<Emits>();

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('uk-UA', {
    style: 'currency',
    currency: 'UAH',
  }).format(price);
};

const columns = [
  {
    id: "main_image",
    header: "",
    meta: { class: { th: "w-[60px]" } },
  },
  {
    id: "name",
    header: "Назва",
  },
  {
    id: "category",
    header: "Категорія",
    meta: { class: { th: "w-[150px]" } },
  },
  {
    id: "brand",
    header: "Бренд",
    meta: { class: { th: "w-[150px]" } },
  },
  {
    id: "base_price",
    header: "Ціна",
    meta: { class: { th: "w-[120px]" } },
  },
  {
    id: "status",
    header: "Статус",
    meta: { class: { th: "w-[120px]" } },
  },
  {
    id: "actions",
    header: "Дії",
    meta: { class: { th: "w-[100px] text-right", td: "text-right" } },
  },
];
</script>
