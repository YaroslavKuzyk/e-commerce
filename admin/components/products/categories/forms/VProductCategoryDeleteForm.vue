<template>
  <div class="space-y-4">
    <div v-if="category" class="p-4 bg-error-50 dark:bg-error-900/20 rounded-lg">
      <p class="text-sm text-error-600 dark:text-error-400">
        Ви збираєтеся видалити категорію:
      </p>
      <p class="font-semibold mt-2">{{ category.name }}</p>
      <p v-if="category.subcategories && category.subcategories.length > 0" class="text-sm text-error-600 dark:text-error-400 mt-2">
        <AlertCircle class="w-4 h-4 inline mr-1" />
        Увага! Це також видалить {{ category.subcategories.length }} підкатегорій
      </p>
    </div>

    <p class="text-sm text-gray-600 dark:text-gray-400">
      Ця дія незворотна. Категорія та всі її підкатегорії будуть видалені з системи.
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
import { Send, Ban, AlertCircle } from "lucide-vue-next";
import type { ProductCategory } from "~/models/productCategory";

interface IProps {
  category: ProductCategory | null;
}

interface IEmits {
  (e: "close"): void;
}

const props = defineProps<IProps>();
const emits = defineEmits<IEmits>();
const toast = useToast();
const productCategoryStore = useProductCategoryStore();

const loading = ref(false);

const onDelete = async () => {
  if (!props.category) return;

  try {
    loading.value = true;

    const { error } = await productCategoryStore.onDeleteProductCategory(
      props.category.id
    );

    if (error.value) {
      toast.add({
        title: "Помилка",
        description: "Не вдалося видалити категорію",
        color: "error",
      });
      return;
    }

    toast.add({
      title: "Успішно",
      description: "Категорію успішно видалено",
      color: "success",
    });

    // Invalidate product-categories cache
    await refreshNuxtData('product-categories');

    emits("close");
  } catch (error: any) {
    toast.add({
      title: "Помилка",
      description: error?.message || "Не вдалося видалити категорію",
      color: "error",
    });
  } finally {
    loading.value = false;
  }
};
</script>
