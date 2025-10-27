<template>
  <slot v-if="hasPermissions" />
</template>

<script setup lang="ts">
import { useAuthStore } from "~/stores/useAuthStore";

interface IProps {
  requiredPermissions: string[];
}

const props = defineProps<IProps>();
const authStore = useAuthStore();

const hasPermissions = computed(() => {
  return props.requiredPermissions.every((requiredPermission) => {
    return authStore.user?.role.permissions.some((permission) => {
      return permission.name === requiredPermission;
    });
  });
});
</script>
