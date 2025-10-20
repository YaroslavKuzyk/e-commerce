<template>
  <div>
    <UTable :data="permissionsData" :columns="columns" class="flex-1">
      <template #actions-cell="{ row }">
        <UCheckbox
          :model-value="selectedPermissionIds.includes(Number(row.original.id))"
          @change="handlePermissionChange(row.original.id)"
        />
      </template>
    </UTable>

    <USeparator />

    <div class="flex justify-end mt-4">
      <UButton @click="emits('close')"> Створити роль </UButton>
    </div>
  </div>
</template>

<script setup lang="ts">
interface IEmits {
  (e: "close"): void;
}

const emits = defineEmits<IEmits>();

const roleStore = useRoleStore();

const { data: permissionsData } = await roleStore.fetchPermissions();

const selectedPermissionIds = ref<number[]>([]);
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
  if (selectedPermissionIds.value.includes(permissionId)) {
    selectedPermissionIds.value = selectedPermissionIds.value.filter(
      (id) => id !== permissionId
    );
  } else {
    selectedPermissionIds.value.push(permissionId);
  }
};
</script>
