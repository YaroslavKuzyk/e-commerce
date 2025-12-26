<template>
  <UModal v-model:open="isOpen">
    <template #content>
      <UCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">
              {{ isEdit ? "Редагувати секцію" : "Додати секцію" }}
            </h3>
            <UButton variant="ghost" icon="i-heroicons-x-mark" @click="close" />
          </div>
        </template>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <UFormField label="Назва" required>
            <UInput
              v-model="form.name"
              placeholder="Введіть назву секції"
              :disabled="isLoading"
              class="w-full"
            />
          </UFormField>

          <UFormField label="Посилання">
            <UInput
              v-model="form.link"
              placeholder="/category/slug"
              :disabled="isLoading"
              class="w-full"
            />
          </UFormField>

          <UFormField label="Іконка">
            <div class="space-y-2">
              <div v-if="form.icon_file_id" class="flex items-center gap-2">
                <VSecureImage
                  :file-id="form.icon_file_id"
                  alt="Icon"
                  width="w-12"
                  height="h-12"
                  object-fit="cover"
                  class="rounded border"
                />
                <UButton
                  type="button"
                  size="sm"
                  variant="ghost"
                  color="error"
                  icon="i-heroicons-trash"
                  @click="form.icon_file_id = null"
                  :disabled="isLoading"
                />
              </div>
              <UButton
                type="button"
                variant="outline"
                icon="i-heroicons-photo"
                :disabled="isLoading"
                @click="openFilePicker"
              >
                {{ form.icon_file_id ? "Змінити іконку" : "Вибрати іконку" }}
              </UButton>
            </div>
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

  <VFilePickerModal
    v-model:is-open="isFilePickerOpen"
    file-type="image"
    :max-files="1"
    @select="handleFileSelect"
  />
</template>

<script setup lang="ts">
import type { CatalogMenuSection } from "~/models/catalogMenu";
import type { IFile } from "~/models/files";
import VSecureImage from "~/components/common/VSecureImage.vue";
import VFilePickerModal from "~/components/files/modals/VFilePickerModal.vue";

interface Props {
  modelValue: boolean;
  section?: CatalogMenuSection | null;
  columnIndex: number;
  isLoading?: boolean;
}

interface Emits {
  (e: "update:modelValue", value: boolean): void;
  (
    e: "submit",
    data: {
      name: string;
      link: string;
      icon_file_id: number | null;
      column_index: number;
    }
  ): void;
}

const props = withDefaults(defineProps<Props>(), {
  section: null,
  isLoading: false,
});

const emits = defineEmits<Emits>();

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emits("update:modelValue", value),
});

const isEdit = computed(() => !!props.section);
const isFilePickerOpen = ref(false);

const form = ref({
  name: "",
  link: "",
  icon_file_id: null as number | null,
});

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      if (props.section) {
        form.value = {
          name: props.section.name,
          link: props.section.link || "",
          icon_file_id: props.section.icon_file_id || null,
        };
      } else {
        form.value = {
          name: "",
          link: "",
          icon_file_id: null,
        };
      }
    }
  }
);

const close = () => {
  isOpen.value = false;
};

const openFilePicker = () => {
  isFilePickerOpen.value = true;
};

const handleFileSelect = (files: IFile[]) => {
  if (files.length > 0) {
    form.value.icon_file_id = files[0].id;
  }
};

const handleSubmit = () => {
  if (!form.value.name.trim()) return;

  emits("submit", {
    name: form.value.name.trim(),
    link: form.value.link.trim(),
    icon_file_id: form.value.icon_file_id,
    column_index: props.columnIndex,
  });
};
</script>
