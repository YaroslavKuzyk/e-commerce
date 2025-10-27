<template>
  <UDashboardPanel resizable>
    <template #header>
      <UDashboardNavbar :title="props.title">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>
      </UDashboardNavbar>
      <UDashboardToolbar>
        <slot name="toolbar" />
      </UDashboardToolbar>
    </template>
    <template #body>
      <slot />
    </template>
  </UDashboardPanel>
</template>

<script setup lang="ts">
interface IProps {
  title: string;
}

const props = defineProps<IProps>();
</script>
