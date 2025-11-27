<template>
  <UForm :schema="schema" :state="state" class="space-y-4" @submit="onSubmit">
    <UFormField label="Назва" name="name" required>
      <UInput v-model="state.name" class="w-[100%]" />
    </UFormField>

    <UTable :data="permissionsData" :columns="columns" class="flex-1">
      <template #actions-cell="{ row }">
        <UCheckbox
          :model-value="state.permissions.includes(Number(row.original.id))"
          @change="handlePermissionChange(row.original.id)"
        />
      </template>
    </UTable>

    <USeparator />

    <div class="flex justify-end mt-4">
      <UButton type="submit">
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
import { Send } from "lucide-vue-next";

interface IEmits {
  (e: "close"): void;
}

const emits = defineEmits<IEmits>();
const toast = useToast();

const schema = z.object({
  name: z.string().min(1),
  permissions: z.array(z.number()),
});

const state = reactive({
  name: "",
  permissions: [] as number[],
});

const roleStore = useRoleStore();

const { data: permissionsData } = await roleStore.fetchPermissions();

const columns = ref([
  {
    header: "Назва",
    accessorKey: "name",
  },
  {
    id: "actions",
    header: "Дія",
    meta: {
      class: {
        th: "w-[100px]",
      },
    },
  },
]);

const handlePermissionChange = (permissionId: number) => {
  if (state.permissions.includes(permissionId)) {
    state.permissions = state.permissions.filter(
      (id: number) => id !== permissionId
    );
  } else {
    state.permissions.push(permissionId);
  }
};

const onSubmit = async (event: any) => {
  try {
    const payload = event.data;
    await roleStore.onCreateRole(payload);
    emits("close");
    toast.add({
      title: "Успішно",
      description: "Роль успішно створена",
      color: "success",
    });
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося створити роль",
      color: "error",
    });
  }
};
</script>
