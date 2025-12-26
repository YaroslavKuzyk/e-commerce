<template>
  <UModal v-model:open="isOpen">
    <template #content>
      <UCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">
              {{ isEdit ? "Редагувати посилання" : "Додати посилання" }}
            </h3>
            <UButton variant="ghost" icon="i-heroicons-x-mark" @click="close" />
          </div>
        </template>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <UFormField label="Назва" required>
            <UInput
              v-model="form.name"
              placeholder="Введіть назву"
              :disabled="isLoading"
              class="w-full"
            />
          </UFormField>

          <UFormField label="Посилання" required>
            <UInput
              v-model="form.link"
              placeholder="/products/category/slug"
              :disabled="isLoading"
              class="w-full"
            />
          </UFormField>

          <div class="flex justify-end gap-2 pt-4">
            <UButton variant="outline" @click="close" :disabled="isLoading">
              Скасувати
            </UButton>
            <UButton type="submit" :loading="isLoading">
              {{ isEdit ? "Зберегти" : "Додати" }}
            </UButton>
          </div>
        </form>
      </UCard>
    </template>
  </UModal>
</template>

<script setup lang="ts">
import type { CatalogMenuItem } from "~/models/catalogMenu";

interface Props {
  modelValue: boolean;
  item?: CatalogMenuItem | null;
  isLoading?: boolean;
}

interface Emits {
  (e: "update:modelValue", value: boolean): void;
  (e: "submit", data: { name: string; link: string }): void;
}

const props = withDefaults(defineProps<Props>(), {
  item: null,
  isLoading: false,
});

const emits = defineEmits<Emits>();

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emits("update:modelValue", value),
});

const isEdit = computed(() => !!props.item);

const form = ref({
  name: "",
  link: "",
});

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      if (props.item) {
        form.value = {
          name: props.item.name,
          link: props.item.link,
        };
      } else {
        form.value = {
          name: "",
          link: "",
        };
      }
    }
  }
);

const close = () => {
  isOpen.value = false;
};

const handleSubmit = () => {
  if (!form.value.name.trim() || !form.value.link.trim()) return;

  emits("submit", {
    name: form.value.name.trim(),
    link: form.value.link.trim(),
  });
};
</script>
