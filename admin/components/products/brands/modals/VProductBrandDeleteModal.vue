<template>
  <UModal
    v-model:open="isOpen"
    title="Видалити бренд"
    description="Підтвердіть видалення бренду."
  >
    <template #body>
      <VProductBrandDeleteForm :brand="brand" @close="closeAndRefresh" />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VProductBrandDeleteForm from "@/components/products/brands/forms/VProductBrandDeleteForm.vue";
import type { ProductBrand } from "~/models/productBrand";

interface IProps {
  brand: ProductBrand | null;
}

interface IEmits {
  (e: "refresh"): void;
}

const props = defineProps<IProps>();
const emits = defineEmits<IEmits>();

const isOpen = defineModel<boolean>("isOpen");

const closeAndRefresh = () => {
  emits("refresh");
  isOpen.value = false;
};
</script>
