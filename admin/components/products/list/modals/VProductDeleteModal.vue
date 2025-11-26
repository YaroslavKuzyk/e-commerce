<template>
  <UModal
    v-model:open="isOpen"
    title="Видалити продукт"
    description="Підтвердіть видалення продукту."
  >
    <template #body>
      <VProductDeleteForm :product="product" @close="closeAndRefresh" />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VProductDeleteForm from "@/components/products/list/forms/VProductDeleteForm.vue";
import type { Product } from "~/models/product";

interface IProps {
  product: Product | null;
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
