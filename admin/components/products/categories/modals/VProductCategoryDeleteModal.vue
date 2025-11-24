<template>
  <UModal
    v-model:open="isOpen"
    title="Видалити категорію"
    description="Підтвердіть видалення категорії."
  >
    <template #body>
      <VProductCategoryDeleteForm :category="category" @close="closeAndRefresh" />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VProductCategoryDeleteForm from "@/components/products/categories/forms/VProductCategoryDeleteForm.vue";
import type { ProductCategory } from "~/models/productCategory";

interface IProps {
  category: ProductCategory | null;
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
