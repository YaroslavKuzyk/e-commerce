<template>
  <UForm :schema="schema" :state="state" class="space-y-4" @submit="onSubmit">
    <UFormField label="Назва (EN)" name="name">
      <UInput v-model="state.name" placeholder="Nova Poshta" class="w-full" />
    </UFormField>

    <UFormField label="Назва (UK)" name="name_uk">
      <UInput v-model="state.name_uk" placeholder="Нова Пошта" class="w-full" />
    </UFormField>

    <UFormField label="Код" name="code">
      <UInput
        v-model="state.code"
        placeholder="nova_poshta"
        class="w-full"
        :disabled="!!deliveryMethod"
      />
    </UFormField>

    <UFormField label="Опис (EN)" name="description">
      <UTextarea
        v-model="state.description"
        placeholder="Pickup from Nova Poshta post office"
        class="w-full"
      />
    </UFormField>

    <UFormField label="Опис (UK)" name="description_uk">
      <UTextarea
        v-model="state.description_uk"
        placeholder="Самовивіз з відділення Нової Пошти"
        class="w-full"
      />
    </UFormField>

    <UFormField label="Має API інтеграцію" name="has_api">
      <UToggle v-model="state.has_api" />
    </UFormField>

    <UFormField label="Порядок сортування" name="sort_order">
      <UInput
        v-model.number="state.sort_order"
        type="number"
        placeholder="0"
        class="w-full"
      />
    </UFormField>

    <UFormField label="Активний" name="is_active">
      <UToggle v-model="state.is_active" />
    </UFormField>

    <USeparator />

    <div class="flex justify-end gap-2">
      <UButton type="button" variant="outline" color="neutral" @click="emits('close')">
        <template #leading>
          <Ban class="w-4 h-4" />
        </template>
        Скасувати
      </UButton>
      <UButton type="submit" :loading="loading">
        <template #leading>
          <Send class="w-4 h-4" />
        </template>
        Підтвердити
      </UButton>
    </div>
  </UForm>
</template>

<script setup lang="ts">
import { z } from "zod";
import { Send, Ban } from "lucide-vue-next";
import type { DeliveryMethod } from "~/models/deliveryMethod";

interface IProps {
  deliveryMethod: DeliveryMethod | null;
}

interface IEmits {
  (e: "close"): void;
}

const props = defineProps<IProps>();
const emits = defineEmits<IEmits>();
const toast = useToast();

const schema = z.object({
  name: z.string().min(1, "Назва є обов'язковою"),
  name_uk: z.string().optional(),
  code: z.string().min(1, "Код є обов'язковим"),
  description: z.string().optional(),
  description_uk: z.string().optional(),
  has_api: z.boolean().optional(),
  sort_order: z.number().optional(),
  is_active: z.boolean().optional(),
});

const state = reactive({
  name: props.deliveryMethod?.name || "",
  name_uk: props.deliveryMethod?.name_uk || "",
  code: props.deliveryMethod?.code || "",
  description: props.deliveryMethod?.description || "",
  description_uk: props.deliveryMethod?.description_uk || "",
  has_api: props.deliveryMethod?.has_api || false,
  sort_order: props.deliveryMethod?.sort_order || 0,
  is_active: props.deliveryMethod?.is_active ?? true,
});

const deliveryMethodStore = useDeliveryMethodStore();
const loading = ref(false);

const onSubmit = async () => {
  if (!props.deliveryMethod) return;

  try {
    loading.value = true;

    await deliveryMethodStore.onUpdateDeliveryMethod(props.deliveryMethod.id, state);
    toast.add({
      title: "Успіх",
      description: "Метод доставки оновлено",
      color: "success",
    });

    emits("close");
  } catch (error: any) {
    toast.add({
      title: "Помилка",
      description: error.message || "Не вдалося зберегти метод доставки",
      color: "error",
    });
  } finally {
    loading.value = false;
  }
};
</script>
