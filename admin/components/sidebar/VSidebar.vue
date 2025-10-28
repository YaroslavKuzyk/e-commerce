<template>
  <UDashboardSidebar
    collapsible
    :ui="{ footer: 'border-t border-default' }"
    :default-size="13"
    :min-size="13"
    :max-size="13"
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

interface MenuItem {
  label: string;
  icon: string;
  to?: string;
  requiredPermissions?: string[];
  children?: MenuItem[];
}

const hasPermissions = (requiredPermissions: string[]): boolean => {
  if (!requiredPermissions || requiredPermissions.length === 0) {
    return true;
  }

  const user = authStore.user;

  if (!user || !user.role || !user.role.permissions) {
    return false;
  }

  return requiredPermissions.every((requiredPermission) => {
    return user.role.permissions.some((permission) => {
      return permission.name === requiredPermission;
    });
  });
};

const menus = computed(() => {
  const allMenus: MenuItem[] = [
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
          label: "Адміністратори",
          icon: "i-lucide-shield-user",
          to: "/users/administrators",
          requiredPermissions: ["Read Admin Users"],
        },
        {
          label: "Покупці",
          icon: "i-lucide-shopping-bag",
          to: "/users/customers",
          requiredPermissions: ["Read Customers"],
        },
        {
          label: "Ролі",
          icon: "i-lucide-contact-round",
          to: "/users/roles",
          requiredPermissions: ["Read Roles", "Read Permissions"],
        },
      ],
    },
  ];

  return allMenus
    .map((menu) => {
      if (menu.children && menu.children.length > 0) {
        const filteredChildren = menu.children.filter((child) => {
          if (child.requiredPermissions) {
            return hasPermissions(child.requiredPermissions);
          }
          return true;
        });

        if (filteredChildren.length === 0) {
          return null;
        }

        return {
          ...menu,
          children: filteredChildren,
        };
      }

      if (menu.requiredPermissions) {
        return hasPermissions(menu.requiredPermissions) ? menu : null;
      }

      return menu;
    })
    .filter((menu): menu is MenuItem => menu !== null);
});

const handleLogout = async () => {
  try {
    await authStore.logout();
    navigateTo("/login");
  } catch (error) {
    console.error("Logout error:", error);
  }
};

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
        onSelect: handleLogout,
      },
    ],
  ];
});
</script>

<style scoped></style>
