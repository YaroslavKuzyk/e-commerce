<template>
  <div class="space-y-4">
    <div v-if="product" class="p-4 bg-error-50 dark:bg-error-900/20 rounded-lg">
      <p class="text-sm text-error-600 dark:text-error-400">
        Ви збираєтеся видалити продукт:
      </p>
      <p class="font-semibold mt-2">{{ product.name }}</p>
      <p v-if="product.variants && product.variants.length > 0" class="text-sm mt-1 text-gray-500">
        Разом з ним буде видалено {{ product.variants.length }} варіацій
      </p>
    </div>

    <p class="text-sm text-gray-600 dark:text-gray-400">
      Ця дія незворотна. Продукт та всі його варіації будуть видалені з системи.
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
import type { Product } from "~/models/product";

interface IProps {
  product: Product | null;
}

interface IEmits {
  (e: "close"): void;
}

const props = defineProps<IProps>();
const emits = defineEmits<IEmits>();
const toast = useToast();
const productStore = useProductStore();

const loading = ref(false);

const onDelete = async () => {
  if (!props.product) return;

  try {
    loading.value = true;

    const { error } = await productStore.onDeleteProduct(
      props.product.id
    );

    if (error.value) {
      toast.add({
        title: "Помилка",
        description: "Не вдалося видалити продукт",
        color: "error",
      });
      return;
    }

    toast.add({
      title: "Успішно",
      description: "Продукт успішно видалено",
      color: "success",
    });

    await refreshNuxtData('products');

    emits("close");
  } catch (error: any) {
    toast.add({
      title: "Помилка",
      description: error?.message || "Не вдалося видалити продукт",
      color: "error",
    });
  } finally {
    loading.value = false;
  }
};
</script>
