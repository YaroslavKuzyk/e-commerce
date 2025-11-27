<template>
  <UDashboardPanel resizable>
    <template #header>
      <div class="flex items-center justify-between border-b border-default pr-4">

      <UDashboardNavbar :title="props.title" class="border-none">
        <template #leading>
          <div class="flex items-center gap-2">
            <UDashboardSidebarCollapse />

          </div>
        </template>
      </UDashboardNavbar>


        <div class="flex items-center gap-2">
          <VLangDropdown />
          <VColorModeSwitch />
        </div>
      </div>


      <UDashboardToolbar>
        <slot name="toolbar" />
      </UDashboardToolbar>
    </template>
    <template #body>
      <div class="flex flex-col h-full relative">
        <div class="flex-1 overflow-y-auto" :class="{ 'pb-[53px]': $slots.pagination }">
          <slot />
        </div>
        <div v-if="$slots.pagination" class="border-t border-default bg-default absolute left-[-24px] h-[53px] flex items-center px-6 z-50" style="width: calc(100% + 48px); bottom: -24px">
          <slot name="pagination" />
        </div>
      </div>
    </template>
  </UDashboardPanel>
</template>

<script setup lang="ts">
import VColorModeSwitch from "~/components/common/VColorModeSwitch.vue";
import VLangDropdown from "~/components/common/VLangDropdown.vue";

interface IProps {
  title: string;
}

const props = defineProps<IProps>();
</script>
