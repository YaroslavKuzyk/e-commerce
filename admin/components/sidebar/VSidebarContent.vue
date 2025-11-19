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
      <div class="flex flex-col h-full relative">
        <div class="flex-1 overflow-y-auto" :class="{ 'pb-[53px]': $slots.pagination }">
          <slot />
        </div>
        <div v-if="$slots.pagination" class="border-t border-default bg-white dark:bg-gray-950 absolute left-[-24px] h-[53px] flex items-center px-6 z-50" style="width: calc(100% + 48px); bottom: -24px">
          <slot name="pagination" />
        </div>
      </div>
    </template>
  </UDashboardPanel>
</template>

<script setup lang="ts">
interface IProps {
  title: string;
}

const props = defineProps<IProps>();
</script>
