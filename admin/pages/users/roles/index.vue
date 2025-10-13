<template>
  <VSidebarContent title="Ролі">
    <template #toolbar>
      <div class="flex items-center justify-between w-full gap-2">
        <USelectMenu
          v-model="selectedRole"
          :items="roles"
          class="max-w-[300px] w-[100%]"
          placeholder="Обрати роль"
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
          <UButton>Додати роль</UButton>
        </div>
      </div>
    </template>

    <UTable :data="permissionsData" :columns="columns" class="flex-1">
      <template #actions-cell="{ row }">
        <UCheckbox />
      </template>
    </UTable>
  </VSidebarContent>
</template>

<script setup lang="ts">
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";

const roles = ref(["Роль 1", "Роль 2", "Роль 3"]);
const selectedRole = ref(null);

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
</script>
