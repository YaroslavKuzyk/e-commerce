<template>
  <UForm :schema="schema" :state="state" class="space-y-4" @submit="onSubmit">
    <UFormField label="Ім'я" name="name" required>
      <UInput v-model="state.name" placeholder="Введіть ім'я" class="w-full" />
    </UFormField>

    <UFormField label="Email" name="email" required>
      <UInput
        v-model="state.email"
        type="email"
        placeholder="admin@example.com"
        class="w-full"
      />
    </UFormField>

    <UFormField label="Роль" name="role_id" required>
      <USelectMenu
        v-model="state.role_id"
        :items="rolesData || []"
        placeholder="Оберіть роль"
        value-key="id"
        label-key="name"
        class="w-full"
      />
    </UFormField>

    <UFormField label="Статус" name="status" required>
      <USelectMenu
        v-model="state.status"
        :items="statusOptions"
        placeholder="Оберіть статус"
        value-key="value"
        label-key="label"
        class="w-full"
      />
    </UFormField>

    <UFormField label="Пароль" name="password" required>
      <UInput
        v-model="state.password"
        type="password"
        placeholder="Введіть пароль (мінімум 8 символів)"
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

interface IEmits {
  (e: "close"): void;
}

const emits = defineEmits<IEmits>();
const toast = useToast();

const schema = z.object({
  name: z.string().min(1, "Ім'я є обов'ązковим"),
  email: z.string().email("Некоректний email"),
  role_id: z.number().min(1, "Роль є обов'язковою"),
  status: z.enum(["active", "inactive"]).optional(),
  password: z
    .string()
    .min(8, "Пароль має містити мінімум 8 символів"),
});

const state = reactive({
  name: "",
  email: "",
  role_id: null as number | null,
  status: "active" as "active" | "inactive",
  password: "",
});

const statusOptions = [
  { label: "Активний", value: "active" },
  { label: "Неактивний", value: "inactive" },
];

const roleStore = useRoleStore();
const adminStore = useAdminStore();

const { data: rolesData } = await roleStore.fetchRoles();

const loading = ref(false);

const onSubmit = async (event: any) => {
  try {
    loading.value = true;
    const payload = { ...event.data };

    // Convert role object to role_id if it's an object
    if (payload.role_id && typeof payload.role_id === "object") {
      payload.role_id = payload.role_id.id;
    }

    await adminStore.onCreateAdmin(payload);

    toast.add({
      title: "Успішно",
      description: "Адміністратора успішно створено",
      color: "success",
    });

    emits("close");

    // Reset form
    state.name = "";
    state.email = "";
    state.role_id = null;
    state.status = "active";
    state.password = "";
  } catch (error: any) {
    toast.add({
      title: "Помилка",
      description: error?.message || "Не вдалося створити адміністратора",
      color: "error",
    });
  } finally {
    loading.value = false;
  }
};
</script>
