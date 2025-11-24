<template>
  <UModal
    :title="category ? 'Редагувати категорію' : 'Створити категорію'"
    :description="category ? 'Оновіть дані категорії' : 'Додайте нову категорію продуктів'"
    v-model:open="isOpen"
  >
    <template #body>
      <VProductCategoryForm
        :category="props.category"
        :all-categories="props.allCategories"
        @close="closeAndRefresh"
      />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VProductCategoryForm from "~/components/products/categories/forms/VProductCategoryForm.vue";
import type { ProductCategory } from "~/models/productCategory";

interface Props {
  category: ProductCategory | null;
  allCategories: ProductCategory[];
}

interface Emits {
  (e: "refresh"): void;
}

const props = defineProps<Props>();
const emits = defineEmits<Emits>();

const isOpen = defineModel<boolean>();

const closeAndRefresh = () => {
  emits("refresh");
  isOpen.value = false;
};
</script>
