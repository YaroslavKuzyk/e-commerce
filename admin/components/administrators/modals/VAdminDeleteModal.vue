<template>
  <UModal
    title="Видалити адміністратора"
    description="Ви впевнені, що хочете видалити цього адміністратора?"
    v-model:open="isOpen"
  >
    <template #body>
      <VAdminDeleteForm :admin="admin" @close="closeAndRefresh" />
    </template>
  </UModal>
</template>

<script setup lang="ts">
import VAdminDeleteForm from "@/components/administrators/forms/VAdminDeleteForm.vue";
import type { IAdmin } from "~/models/administrators";

interface IProps {
  admin: IAdmin | null;
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
