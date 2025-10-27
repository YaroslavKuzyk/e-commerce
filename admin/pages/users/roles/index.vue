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
          <UButton color="error" @click="isDeleteRoleModalOpen = true"
            >Видалити роль</UButton
          >
          <UButton @click="isCreateRoleModalOpen = true">Додати роль</UButton>
        </div>
      </div>
    </template>

    <VRoleUpdateForm
      :selected-role="selectedRoleData"
      :permissions-data="permissionsData || []"
      @refresh="refreshRoles"
    />
    <VRoleCreateModal
      v-model:is-open="isCreateRoleModalOpen"
      @refresh="refreshRoles"
    />
    <VRoleDeleteModal
      v-model:is-open="isDeleteRoleModalOpen"
      @refresh="refreshRoles"
    />
  </VSidebarContent>
</template>

<script setup lang="ts">
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VRoleUpdateForm from "~/components/roles/forms/VRoleUpdateForm.vue";
import VRoleCreateModal from "~/components/roles/modals/VRoleCreateModal.vue";
import VRoleDeleteModal from "~/components/roles/modals/VRoleDeleteModal.vue";

definePageMeta({
  middleware: ["sanctum:auth"],
});

const roleStore = useRoleStore();

const { data: permissionsData } = await roleStore.fetchPermissions();
const { data: rolesData, refresh: refreshRolesData } =
  await roleStore.fetchRoles();

const refreshRoles = async () => {
  await refreshRolesData();
};

const selectedRole = ref(null);
const isCreateRoleModalOpen = ref(false);
const isDeleteRoleModalOpen = ref(false);

const selectedRoleData = computed(() => {
  if (!selectedRole.value || !rolesData.value) return null;
  return rolesData.value.find((role: any) => role.id === selectedRole.value);
});
</script>
