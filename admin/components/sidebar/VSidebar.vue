<template>
  <UDashboardSidebar
    collapsible
    :ui="{ footer: 'border-t border-default' }"
    :default-size="50"
  >
    <template #header="{ collapsed }">
      <div v-if="!collapsed" class="flex items-center gap-2">
        <Slack class="h-5 w-auto shrink-0" />
        Admin E-Commerce
      </div>
      <div v-else class="flex items-center justify-center w-full">
        <Slack class="h-5 w-auto shrink-0" />
      </div>
    </template>

    <template #default="{ collapsed }">
      <UNavigationMenu
        :collapsed="collapsed"
        :popover="true"
        :items="menus"
        orientation="vertical"
      />
    </template>

    <template #footer="{ collapsed }">
      <UDropdownMenu :items="dropdownItems">
        <UButton
          :avatar="{
            src: 'https://github.com/benjamincanac.png',
          }"
          :label="collapsed ? undefined : authStore.user?.name"
          color="neutral"
          variant="ghost"
          class="w-full"
          :block="collapsed"
        >
          <template #trailing>
            <ChevronsUpDown class="size-4 ml-auto" />
          </template>
        </UButton>
      </UDropdownMenu>
    </template>
  </UDashboardSidebar>
</template>

<script setup lang="ts">
import { useAuthStore } from "~/stores/useAuthStore";
import { Slack, ChevronsUpDown } from "lucide-vue-next";

const authStore = useAuthStore();

const menus = computed(() => {
  return [
    {
      label: "Головна",
      icon: "i-lucide-home",
      to: "/",
    },
    {
      label: "Користувачі",
      icon: "i-lucide-users",
      children: [
        {
          label: "Список Адміністраторів",
          icon: "i-lucide-shield-user",
          to: "/users/administrators",
        },
        {
          label: "Ролі",
          icon: "i-lucide-contact-round",
          to: "/users/roles",
        },
      ],
    },
  ];
});

const dropdownItems = computed(() => {
  return [
    [
      {
        label: authStore.user?.name,
        avatar: {
          src: "https://github.com/benjamincanac.png",
        },
        type: "label",
      },
    ],
    [
      {
        label: "Профіль",
        icon: "i-lucide-user",
      },
      {
        label: "Налаштування",
        icon: "i-lucide-cog",
      },
    ],
    [
      {
        label: "Вихід",
        icon: "i-lucide-log-out",
      },
    ],
  ];
});
</script>

<style scoped></style>
