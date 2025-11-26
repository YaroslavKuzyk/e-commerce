<template>
  <div class="space-y-4">
    <div v-if="attribute" class="p-4 bg-error-50 dark:bg-error-900/20 rounded-lg">
      <p class="text-sm text-error-600 dark:text-error-400">
        Ви збираєтеся видалити атрибут:
      </p>
      <p class="font-semibold mt-2">{{ attribute.name }}</p>
      <p v-if="attribute.values && attribute.values.length > 0" class="text-sm mt-1 text-gray-500">
        Разом з ним буде видалено {{ attribute.values.length }} значень
      </p>
    </div>

    <p class="text-sm text-gray-600 dark:text-gray-400">
      Ця дія незворотна. Атрибут буде видалено з системи.
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
import type { Attribute } from "~/models/attribute";

interface IProps {
  attribute: Attribute | null;
}

interface IEmits {
  (e: "close"): void;
}

const props = defineProps<IProps>();
const emits = defineEmits<IEmits>();
const toast = useToast();
const attributeStore = useAttributeStore();

const loading = ref(false);

const onDelete = async () => {
  if (!props.attribute) return;

  try {
    loading.value = true;

    const { error } = await attributeStore.onDeleteAttribute(
      props.attribute.id
    );

    if (error.value) {
      toast.add({
        title: "Помилка",
        description: "Не вдалося видалити атрибут",
        color: "error",
      });
      return;
    }

    toast.add({
      title: "Успішно",
      description: "Атрибут успішно видалено",
      color: "success",
    });

    await refreshNuxtData('attributes');

    emits("close");
  } catch (error: any) {
    toast.add({
      title: "Помилка",
      description: error?.message || "Не вдалося видалити атрибут",
      color: "error",
    });
  } finally {
    loading.value = false;
  }
};
</script>
