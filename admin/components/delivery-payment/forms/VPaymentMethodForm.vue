<template>
  <UForm :schema="schema" :state="state" class="space-y-4" @submit="onSubmit">
    <UFormField label="Назва (EN)" name="name">
      <UInput v-model="state.name" placeholder="Monopay" class="w-full" />
    </UFormField>

    <UFormField label="Назва (UK)" name="name_uk">
      <UInput v-model="state.name_uk" placeholder="Monopay" class="w-full" />
    </UFormField>

    <UFormField label="Код" name="code">
      <UInput
        v-model="state.code"
        placeholder="monopay"
        class="w-full"
        :disabled="!!paymentMethod"
      />
    </UFormField>

    <UFormField label="Опис (EN)" name="description">
      <UTextarea
        v-model="state.description"
        placeholder="Pay online with Monopay"
        class="w-full"
      />
    </UFormField>

    <UFormField label="Опис (UK)" name="description_uk">
      <UTextarea
        v-model="state.description_uk"
        placeholder="Онлайн оплата через Monopay"
        class="w-full"
      />
    </UFormField>

    <UFormField label="Тип" name="type">
      <USelectMenu
        v-model="state.type"
        :items="typeOptions"
        placeholder="Оберіть тип"
        value-key="value"
        label-key="label"
        class="w-full"
      />
    </UFormField>

    <UFormField
      v-if="state.type === 'online'"
      label="Провайдер"
      name="provider"
    >
      <UInput
        v-model="state.provider"
        placeholder="monopay, liqpay"
        class="w-full"
      />
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
      <UButton type="button" variant="outline" @click="emits('close')">
        Скасувати
      </UButton>
      <UButton type="submit" :loading="loading">
        {{ paymentMethod ? "Оновити" : "Створити" }}
      </UButton>
    </div>
  </UForm>
</template>

<script setup lang="ts">
import { z } from "zod";
import type { PaymentMethod } from "~/models/paymentMethod";

interface IProps {
  paymentMethod?: PaymentMethod | null;
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
  type: z.enum(["cash_on_delivery", "online"]),
  provider: z.string().optional(),
  sort_order: z.number().optional(),
  is_active: z.boolean().optional(),
});

const typeOptions = [
  { label: "Оплата при отриманні", value: "cash_on_delivery" },
  { label: "Онлайн оплата", value: "online" },
];

const state = reactive({
  name: props.paymentMethod?.name || "",
  name_uk: props.paymentMethod?.name_uk || "",
  code: props.paymentMethod?.code || "",
  description: props.paymentMethod?.description || "",
  description_uk: props.paymentMethod?.description_uk || "",
  type: (props.paymentMethod?.type as "cash_on_delivery" | "online") || "cash_on_delivery",
  provider: props.paymentMethod?.provider || "",
  sort_order: props.paymentMethod?.sort_order || 0,
  is_active: props.paymentMethod?.is_active ?? true,
});

const paymentMethodStore = usePaymentMethodStore();
const loading = ref(false);

const onSubmit = async () => {
  try {
    loading.value = true;

    if (props.paymentMethod) {
      await paymentMethodStore.updatePaymentMethod(props.paymentMethod.id, state);
      toast.add({
        title: "Успіх",
        description: "Метод оплати оновлено",
        color: "green",
      });
    } else {
      await paymentMethodStore.createPaymentMethod(state);
      toast.add({
        title: "Успіх",
        description: "Метод оплати створено",
        color: "green",
      });
    }

    emits("close");
  } catch (error: any) {
    toast.add({
      title: "Помилка",
      description: error.message || "Не вдалося зберегти метод оплати",
      color: "red",
    });
  } finally {
    loading.value = false;
  }
};
</script>
