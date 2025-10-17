<template>
  <VSidebarContent title="Ролі">
    <template #toolbar>
      <div class="flex items-center justify-between w-full gap-2">
        <USelectMenu
          v-model="selectedRole"
          :items="rolesData"
          class="max-w-[300px] w-[100%]"
          placeholder="Обрати роль"
          value-key="id"
          label-key="name"
        >
          <template #trailing>
            <UButton
              v-if="selectedRole"
              size="sm"
              variant="link"
              icon="i-lucide-circle-x"
              aria-label="Очистити"
              @click.stop="selectedRole = null"
              color="neutral"
            />
          </template>
        </USelectMenu>
        <div class="flex items-center gap-2 shrink-0">
          <UButton color="error">Видалити роль</UButton>
          <UButton @click="isCreateRoleModalOpen = true">Додати роль</UButton>
        </div>
      </div>
    </template>

    <UTable :data="permissionsData" :columns="columns" class="flex-1">
      <template #actions-cell="{ row }">
        <UCheckbox
          :model-value="selectedPermissionIds.includes(Number(row.original.id))"
          @change="handlePermissionChange(row.original.id)"
        />
      </template>
    </UTable>

    <UModal
      title="Створити нову роль"
      description="Створіть нову роль для адміністратора."
      v-model:open="isCreateRoleModalOpen"
    >
      <template #body>
        <UTable :data="permissionsData" :columns="columns" class="flex-1">
          <template #actions-cell="{ row }">
            <UCheckbox
              :model-value="
                selectedPermissionIds.includes(Number(row.original.id))
              "
              @change="handlePermissionChange(row.original.id)"
            />
          </template>
        </UTable>

        <USeparator />

        <div class="flex justify-end mt-4">
          <UButton @click="isCreateRoleModalOpen = false">
            Створити роль
          </UButton>
        </div>
      </template>
    </UModal>
  </VSidebarContent>
</template>

<script setup lang="ts">
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";

definePageMeta({
  middleware: ["sanctum:auth"],
});

const roleStore = useRoleStore();

const { data: permissionsData } = await roleStore.fetchPermissions();
const { data: rolesData } = await roleStore.fetchRoles();

const selectedRole = ref(null);
const selectedPermissionIds = ref<number[]>([]);
const isCreateRoleModalOpen = ref(false);

watch(
  () => selectedRole.value,
  (value) => {
    if (!value || !rolesData.value) return [];

    const role = rolesData.value.find((role) => role.id === value);
    selectedPermissionIds.value =
      role?.permissions?.map((permission) => permission.id) || [];
  }
);

const handlePermissionChange = (permissionId: number) => {
  if (selectedPermissionIds.value.includes(permissionId)) {
    selectedPermissionIds.value = selectedPermissionIds.value.filter(
      (id) => id !== permissionId
    );
  } else {
    selectedPermissionIds.value.push(permissionId);
  }
};

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
</script>
