<template>
  <VSidebarContent title="Ролі">
    <template #toolbar>
      <div class="flex items-center justify-between w-full gap-2">
        <HasPermissions :required-permissions="['Read Roles']">
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
                aria-label="Очистити"
                @click.stop="selectedRole = null"
                color="neutral"
              >
                <CircleX class="w-4 h-4" />
              </UButton>
            </template>
          </USelectMenu>
        </HasPermissions>
        <div class="flex items-center gap-2 shrink-0">
          <HasPermissions :required-permissions="['Delete Role']">
            <UButton color="error" variant="outline" @click="isDeleteRoleModalOpen = true">
              <template #leading>
                <Trash2 class="w-4 h-4" />
              </template>
              Видалити роль
            </UButton>
          </HasPermissions>
          <HasPermissions
            :required-permissions="['Create Role', 'Read Permissions']"
          >
            <UButton @click="isCreateRoleModalOpen = true">
              <template #leading>
                <Plus class="w-4 h-4" />
              </template>
              Додати роль
            </UButton>
          </HasPermissions>
        </div>
      </div>
    </template>

    <HasPermissions :required-permissions="['Read Roles', 'Read Permissions']">
      <VRoleUpdateForm
        :selected-role="selectedRoleData"
        :permissions-data="permissionsData || []"
        @refresh="refreshRoles"
      />
    </HasPermissions>

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
import { CircleX, Plus, Trash2 } from "lucide-vue-next";
import HasPermissions from "~/components/common/VHasPermissions.vue";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VRoleUpdateForm from "~/components/roles/forms/VRoleUpdateForm.vue";
import VRoleCreateModal from "~/components/roles/modals/VRoleCreateModal.vue";
import VRoleDeleteModal from "~/components/roles/modals/VRoleDeleteModal.vue";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Read Roles", "Read Permissions"],
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
