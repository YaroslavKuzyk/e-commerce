<template>
  <UModal
    v-model:open="isOpen"
    title="Видалити атрибут"
    description="Підтвердіть видалення атрибута."
  >
    <template #body>
      <VAttributeDeleteForm :attribute="attribute" @close="closeAndRefresh" />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VAttributeDeleteForm from "@/components/products/attributes/forms/VAttributeDeleteForm.vue";
import type { Attribute } from "~/models/attribute";

interface IProps {
  attribute: Attribute | null;
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
