<template>
  <div class="space-y-4">
    <div v-if="brand" class="p-4 bg-error-50 dark:bg-error-900/20 rounded-lg">
      <p class="text-sm text-error-600 dark:text-error-400">
        Ви збираєтеся видалити бренд:
      </p>
      <p class="font-semibold mt-2">{{ brand.name }}</p>
    </div>

    <p class="text-sm text-gray-600 dark:text-gray-400">
      Ця дія незворотна. Бренд буде видалено з системи.
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
import type { ProductBrand } from "~/models/productBrand";

interface IProps {
  brand: ProductBrand | null;
}

interface IEmits {
  (e: "close"): void;
}

const props = defineProps<IProps>();
const emits = defineEmits<IEmits>();
const toast = useToast();
const productBrandStore = useProductBrandStore();

const loading = ref(false);

const onDelete = async () => {
  if (!props.brand) return;

  try {
    loading.value = true;

    const { error } = await productBrandStore.onDeleteProductBrand(
      props.brand.id
    );

    if (error.value) {
      toast.add({
        title: "Помилка",
        description: "Не вдалося видалити бренд",
        color: "error",
      });
      return;
    }

    toast.add({
      title: "Успішно",
      description: "Бренд успішно видалено",
      color: "success",
    });

    // Invalidate product-brands cache
    await refreshNuxtData('product-brands');

    emits("close");
  } catch (error: any) {
    toast.add({
      title: "Помилка",
      description: error?.message || "Не вдалося видалити бренд",
      color: "error",
    });
  } finally {
    loading.value = false;
  }
};
</script>
