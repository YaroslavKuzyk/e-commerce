<template>
  <div class="space-y-6 pb-8">
    <div class="flex items-center justify-between pb-2 border-b border-gray-200 dark:border-gray-700">
      <h3 class="font-medium text-lg">Характеристики продукту</h3>
      <UButton
        icon="i-heroicons-plus"
        @click="openAddModal"
      >
        Додати характеристику
      </UButton>
    </div>

    <div v-if="localSpecifications.length === 0" class="text-center py-8 text-gray-500">
      <UIcon name="i-heroicons-list-bullet" class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" />
      <p>Немає характеристик. Додайте першу характеристику продукту.</p>
    </div>

    <draggable
      v-else
      v-model="localSpecifications"
      item-key="id"
      handle=".drag-handle"
      ghost-class="opacity-50"
      class="space-y-2"
      @change="handleDragEnd"
    >
      <template #item="{ element: spec, index }">
        <div
          class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-sm transition-shadow"
        >
          <div class="flex items-center gap-2 text-gray-400">
            <UIcon name="i-heroicons-bars-3" class="w-4 h-4 cursor-move drag-handle" />
            <span class="text-sm font-medium text-gray-500 w-6">{{ index + 1 }}</span>
          </div>
          <div class="flex-1 grid grid-cols-2 gap-4">
            <div>
              <span class="text-xs text-gray-500 dark:text-gray-400">Назва</span>
              <p class="font-medium text-gray-900 dark:text-white">{{ spec.name }}</p>
            </div>
            <div>
              <span class="text-xs text-gray-500 dark:text-gray-400">Значення</span>
              <p class="text-gray-700 dark:text-gray-300">{{ spec.value }}</p>
            </div>
          </div>
          <div class="flex items-center gap-1">
            <UButton
              size="sm"
              variant="ghost"
              icon="i-heroicons-pencil"
              @click="openEditModal(spec)"
            />
            <UButton
              size="sm"
              variant="ghost"
              color="error"
              icon="i-heroicons-trash"
              @click="openDeleteModal(spec)"
            />
          </div>
        </div>
      </template>
    </draggable>

    <!-- Add/Edit Modal -->
    <UModal v-model:open="isModalOpen" :title="editingSpec ? 'Редагувати характеристику' : 'Додати характеристику'">
      <template #body>
        <UForm :schema="schema" :state="formState" class="space-y-4 p-4" @submit="handleSubmit">
          <UFormField label="Назва характеристики" name="name">
            <UInput
              v-model="formState.name"
              placeholder="Наприклад: Матеріал, Вага, Розмір"
              class="w-full"
            />
          </UFormField>

          <UFormField label="Значення" name="value">
            <UInput
              v-model="formState.value"
              placeholder="Наприклад: Бавовна, 250г, XL"
              class="w-full"
            />
          </UFormField>

          <div class="flex justify-end gap-2 pt-2">
            <UButton
              type="button"
              variant="outline"
              color="neutral"
              @click="closeModal"
            >
              Скасувати
            </UButton>
            <UButton
              type="submit"
              :loading="submitLoading"
            >
              {{ editingSpec ? 'Зберегти' : 'Додати' }}
            </UButton>
          </div>
        </UForm>
      </template>
    </UModal>

    <!-- Delete Modal -->
    <UModal v-model:open="isDeleteModalOpen" title="Видалити характеристику">
      <template #body>
        <div class="space-y-4 p-4">
          <div v-if="deletingSpec" class="p-4 bg-error-50 dark:bg-error-900/20 rounded-lg">
            <p class="text-sm text-error-600 dark:text-error-400">
              Ви збираєтеся видалити характеристику:
            </p>
            <p class="font-semibold mt-2">{{ deletingSpec.name }}: {{ deletingSpec.value }}</p>
          </div>

          <p class="text-sm text-gray-600 dark:text-gray-400">
            Ця дія незворотна.
          </p>

          <div class="flex justify-end gap-2">
            <UButton
              variant="outline"
              color="neutral"
              @click="isDeleteModalOpen = false"
            >
              Скасувати
            </UButton>
            <UButton
              color="error"
              :loading="deleteLoading"
              @click="handleDelete"
            >
              Видалити
            </UButton>
          </div>
        </div>
      </template>
    </UModal>
  </div>
</template>

<script setup lang="ts">
import { z } from "zod";
import draggable from "vuedraggable";
import type { Product, ProductSpecification } from "~/models/product";

interface Props {
  product: Product;
}

interface Emits {
  (e: "refresh"): void;
}

const props = defineProps<Props>();
const emits = defineEmits<Emits>();
const toast = useToast();
const productStore = useProductStore();

const schema = z.object({
  name: z.string().min(1, "Назва є обов'язковою"),
  value: z.string().min(1, "Значення є обов'язковим"),
});

// Local copy for drag-and-drop
const localSpecifications = ref<ProductSpecification[]>([]);

// Sync local specifications with props
watch(
  () => props.product.specifications,
  (newSpecs) => {
    localSpecifications.value = [...newSpecs];
  },
  { immediate: true, deep: true }
);

const isModalOpen = ref(false);
const editingSpec = ref<ProductSpecification | null>(null);
const submitLoading = ref(false);

const formState = reactive({
  name: "",
  value: "",
});

const isDeleteModalOpen = ref(false);
const deletingSpec = ref<ProductSpecification | null>(null);
const deleteLoading = ref(false);

const handleDragEnd = async () => {
  const specificationIds = localSpecifications.value.map(spec => spec.id);

  try {
    const { data, error } = await productStore.onReorderSpecifications(props.product.id, specificationIds);

    if (error.value) {
      toast.add({
        title: "Помилка",
        description: "Не вдалося змінити порядок характеристик",
        color: "error",
      });
      // Revert to original order
      localSpecifications.value = [...props.product.specifications];
      return;
    }

    toast.add({
      title: "Успішно",
      description: "Порядок характеристик змінено",
      color: "success",
    });

    emits("refresh");
  } catch (err) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося змінити порядок характеристик",
      color: "error",
    });
    // Revert to original order
    localSpecifications.value = [...props.product.specifications];
  }
};

const openAddModal = () => {
  editingSpec.value = null;
  formState.name = "";
  formState.value = "";
  isModalOpen.value = true;
};

const openEditModal = (spec: ProductSpecification) => {
  editingSpec.value = spec;
  formState.name = spec.name;
  formState.value = spec.value;
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  editingSpec.value = null;
  formState.name = "";
  formState.value = "";
};

const handleSubmit = async () => {
  submitLoading.value = true;

  try {
    if (editingSpec.value) {
      const { error } = await productStore.onUpdateSpecification(
        props.product.id,
        editingSpec.value.id,
        {
          name: formState.name,
          value: formState.value,
        }
      );

      if (error.value) {
        toast.add({
          title: "Помилка",
          description: "Не вдалося оновити характеристику",
          color: "error",
        });
        return;
      }

      toast.add({
        title: "Успішно",
        description: "Характеристику оновлено",
        color: "success",
      });
    } else {
      const { error } = await productStore.onAddSpecification(props.product.id, {
        name: formState.name,
        value: formState.value,
      });

      if (error.value) {
        toast.add({
          title: "Помилка",
          description: "Не вдалося додати характеристику",
          color: "error",
        });
        return;
      }

      toast.add({
        title: "Успішно",
        description: "Характеристику додано",
        color: "success",
      });
    }

    closeModal();
    emits("refresh");
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося зберегти характеристику",
      color: "error",
    });
  } finally {
    submitLoading.value = false;
  }
};

const openDeleteModal = (spec: ProductSpecification) => {
  deletingSpec.value = spec;
  isDeleteModalOpen.value = true;
};

const handleDelete = async () => {
  if (!deletingSpec.value) return;

  deleteLoading.value = true;

  try {
    const { error } = await productStore.onDeleteSpecification(
      props.product.id,
      deletingSpec.value.id
    );

    if (error.value) {
      toast.add({
        title: "Помилка",
        description: "Не вдалося видалити характеристику",
        color: "error",
      });
      return;
    }

    toast.add({
      title: "Успішно",
      description: "Характеристику видалено",
      color: "success",
    });

    isDeleteModalOpen.value = false;
    deletingSpec.value = null;
    emits("refresh");
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося видалити характеристику",
      color: "error",
    });
  } finally {
    deleteLoading.value = false;
  }
};
</script>
