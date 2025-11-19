<template>
  <div class="space-y-4">
    <div v-if="customer" class="p-4 bg-error-50 dark:bg-error-900/20 rounded-lg">
      <p class="text-sm text-error-600 dark:text-error-400">
        Ви збираєтеся видалити покупця:
      </p>
      <p class="font-semibold mt-2">{{ customer.name }}</p>
      <p class="text-sm text-gray-600 dark:text-gray-400">
        {{ customer.email }}
      </p>
    </div>

    <p class="text-sm text-gray-600 dark:text-gray-400">
      Ця дія незворотна. Всі дані покупця будуть видалені.
    </p>

    <USeparator />

    <div class="flex justify-end gap-2">
      <UButton type="button" variant="outline" color="neutral" @click="emits('close')">
        <template #leading>
          <Ban class="w-4 h-4" />
        </template>
        Скасувати
      </UButton>
      <UButton
        type="button"
        color="error"
        :loading="loading"
        @click="onDelete"
      >
        <template #leading>
          <Send class="w-4 h-4" />
        </template>
        Підтвердити
      </UButton>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Send, Ban } from "lucide-vue-next";
import type { ICustomer } from "~/models/customers";

interface IProps {
  customer: ICustomer | null;
}

interface IEmits {
  (e: "close"): void;
}

const props = defineProps<IProps>();
const emits = defineEmits<IEmits>();
const toast = useToast();

const customerStore = useCustomerStore();

const loading = ref(false);

const onDelete = async () => {
  if (!props.customer) return;

  try {
    loading.value = true;

    await customerStore.onDeleteCustomer(props.customer.id);

    toast.add({
      title: "Успішно",
      description: "Покупця успішно видалено",
      color: "success",
    });

    emits("close");
  } catch (error: any) {
    toast.add({
      title: "Помилка",
      description: error?.message || "Не вдалося видалити покупця",
      color: "error",
    });
  } finally {
    loading.value = false;
  }
};
</script>
