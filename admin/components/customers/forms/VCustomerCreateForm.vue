<template>
  <UForm :schema="schema" :state="state" class="space-y-4" @submit="onSubmit">
    <UFormField label="Ім'я" name="name">
      <UInput v-model="state.name" placeholder="Введіть ім'я" class="w-full" />
    </UFormField>

    <UFormField label="Email" name="email">
      <UInput
        v-model="state.email"
        type="email"
        placeholder="customer@example.com"
        class="w-full"
      />
    </UFormField>

    <UFormField label="Статус" name="status">
      <USelectMenu
        v-model="state.status"
        :items="statusOptions"
        placeholder="Оберіть статус"
        value-key="value"
        label-key="label"
        class="w-full"
      />
    </UFormField>

    <UFormField label="Пароль" name="password">
      <UInput
        v-model="state.password"
        type="password"
        placeholder="Введіть пароль (мінімум 8 символів)"
        class="w-full"
      />
    </UFormField>

    <USeparator />

    <div class="flex justify-end gap-2">
      <UButton type="button" variant="outline" @click="emits('close')">
        Скасувати
      </UButton>
      <UButton type="submit" :loading="loading"> Створити </UButton>
    </div>
  </UForm>
</template>

<script setup lang="ts">
import { z } from "zod";

interface IEmits {
  (e: "close"): void;
}

const emits = defineEmits<IEmits>();
const toast = useToast();

const schema = z.object({
  name: z.string().min(1, "Ім'я є обов'язковим"),
  email: z.string().email("Некоректний email"),
  status: z.enum(["active", "inactive"]).optional(),
  password: z
    .string()
    .min(8, "Пароль має містити мінімум 8 символів"),
});

const state = reactive({
  name: "",
  email: "",
  status: "active" as "active" | "inactive",
  password: "",
});

const statusOptions = [
  { label: "Активний", value: "active" },
  { label: "Неактивний", value: "inactive" },
];

const customerStore = useCustomerStore();

const loading = ref(false);

const onSubmit = async (event: any) => {
  try {
    loading.value = true;
    const payload = { ...event.data };

    await customerStore.onCreateCustomer(payload);

    toast.add({
      title: "Успішно",
      description: "Покупця успішно створено",
      color: "success",
    });

    emits("close");

    // Reset form
    state.name = "";
    state.email = "";
    state.status = "active";
    state.password = "";
  } catch (error: any) {
    toast.add({
      title: "Помилка",
      description: error?.message || "Не вдалося створити покупця",
      color: "error",
    });
  } finally {
    loading.value = false;
  }
};
</script>
