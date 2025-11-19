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

    <UFormField
      label="Новий пароль (необов'язково)"
      name="password"
      help="Залиште порожнім, якщо не хочете змінювати пароль"
    >
      <UInput
        v-model="state.password"
        type="password"
        placeholder="Новий пароль"
        class="w-full"
      />
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
import type { ICustomer } from "~/models/customers";

interface IProps {
  customer: ICustomer | null;
}

interface IEmits {
  (e: "close"): void;
}

const props = defineProps<IProps>();
const emits = defineEmits<IEmits>();
const toast = useToast();

const schema = z.object({
  name: z.string().min(1, "Ім'я є обов'язковим"),
  email: z.string().email("Некоректний email"),
  status: z.enum(["active", "inactive"]),
  password: z
    .string()
    .min(8, "Пароль має містити мінімум 8 символів")
    .optional()
    .or(z.literal("")),
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

// Watch for customer prop changes to populate form
watch(
  () => props.customer,
  (newCustomer) => {
    if (newCustomer) {
      state.name = newCustomer.name;
      state.email = newCustomer.email;
      state.status = newCustomer.status;
      state.password = "";
    }
  },
  { immediate: true }
);

const onSubmit = async (event: any) => {
  if (!props.customer) return;

  try {
    loading.value = true;
    const payload = {
      customerId: props.customer.id,
      ...event.data,
    };

    // Remove password if empty (don't update it)
    if (!payload.password) {
      delete payload.password;
    }

    await customerStore.onUpdateCustomer(payload);

    toast.add({
      title: "Успішно",
      description: "Покупця успішно оновлено",
      color: "success",
    });

    emits("close");
  } catch (error: any) {
    toast.add({
      title: "Помилка",
      description: error?.message || "Не вдалося оновити покупця",
      color: "error",
    });
  } finally {
    loading.value = false;
  }
};
</script>
