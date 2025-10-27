<template>
  <UForm :schema="schema" :state="state" class="space-y-4" @submit="onSubmit">
    <UTable
      v-if="selectedRole"
      :data="selectedRole ? permissionsData : []"
      :columns="columns"
      class="flex-1"
    >
      <template #actions-cell="{ row }">
        <UCheckbox
          :model-value="state.permissions.includes(Number(row.original.id))"
          @change="handlePermissionChange(row.original.id)"
        />
      </template>
    </UTable>
    <HasPermissions :required-permissions="['Update Role']">
      <div v-if="selectedRole" class="flex justify-end items-end gap-2">
        <UFormField label="Назва" name="name">
          <UInput v-model="state.name" class="w-[100%]" />
        </UFormField>
        <UButton type="submit">Оновити роль</UButton>
      </div>
    </HasPermissions>
  </UForm>
</template>

<script setup lang="ts">
import { z } from "zod";
import HasPermissions from "~/components/common/VHasPermissions.vue";

interface Props {
  selectedRole: any;
  permissionsData: any[];
}

interface IEmits {
  (e: "refresh"): void;
}

const props = defineProps<Props>();
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

watch(
  () => props.selectedRole,
  (value) => {
    if (!value) {
      state.name = "";
      state.permissions = [];
      return;
    }

    state.permissions =
      value.permissions?.map((permission: any) => permission.id) || [];
    state.name = value.name || "";
  },
  { immediate: true }
);

const handlePermissionChange = (permissionId: number) => {
  if (state.permissions.includes(permissionId)) {
    state.permissions = state.permissions.filter((id) => id !== permissionId);
  } else {
    state.permissions.push(permissionId);
  }
};

const onSubmit = async (event: any) => {
  try {
    if (!props.selectedRole) return;

    const payload = event.data;
    await roleStore.onUpdateRole({
      roleId: props.selectedRole.id,
      name: payload.name,
      permissions: payload.permissions,
    });

    toast.add({
      title: "Успішно",
      description: "Роль успішно оновлена",
      color: "success",
    });

    emits("refresh");
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося оновити роль",
      color: "error",
    });
  }
};
</script>

<style scoped></style>
