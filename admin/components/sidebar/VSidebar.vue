<template>
  <UDashboardSidebar
    collapsible
    :ui="{ footer: 'border-t border-default' }"
    resizable
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
        <template #default>
          <UButton
            color="neutral"
            variant="ghost"
            class="w-full"
            :block="collapsed"
          >
            <template #leading>
              <VAvatar
                :name="authStore.user?.name || 'User'"
                :file-id="authStore.user?.avatar_file_id"
                size="xs"
                shape="circle"
              />
            </template>
            <template v-if="!collapsed" #default>
              {{ authStore.user?.name }}
            </template>
            <template v-if="!collapsed" #trailing>
              <ChevronsUpDown class="size-4 ml-auto" />
            </template>
          </UButton>
        </template>
        <template #account>
          <div class="flex items-center gap-3 px-2 py-2">
            <VAvatar
              :name="authStore.user?.name || 'User'"
              :file-id="authStore.user?.avatar_file_id"
              size="sm"
              shape="circle"
            />
            <div class="flex flex-col items-start min-w-0 flex-1">
              <p
                class="text-sm font-semibold text-gray-900 dark:text-white truncate"
              >
                {{ authStore.user?.name || "User" }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                {{ authStore.user?.email || "" }}
              </p>
            </div>
          </div>
        </template>
      </UDropdownMenu>
    </template>
  </UDashboardSidebar>
</template>

<script setup lang="ts">
import { useAuthStore } from "~/stores/useAuthStore";
import {
  Slack,
  ChevronsUpDown,
  Home,
  Users,
  ShieldCheck,
  ShoppingBag,
  ContactRound,
  Truck,
  Folder,
  User,
  Settings,
  LogOut,
  Package,
  FolderTree,
  Tag,
  List,
  SlidersHorizontal,
} from "lucide-vue-next";
import type { Component } from "vue";

const authStore = useAuthStore();

interface MenuItem {
  label: string;
  icon: Component;
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
      icon: Home,
      to: "/",
    },
    {
      label: "Користувачі",
      icon: Users,
      children: [
        {
          label: "Адміністратори",
          icon: ShieldCheck,
          to: "/users/administrators",
          requiredPermissions: ["Read Admin Users"],
        },
        {
          label: "Покупці",
          icon: ShoppingBag,
          to: "/users/customers",
          requiredPermissions: ["Read Customers"],
        },
        {
          label: "Ролі",
          icon: ContactRound,
          to: "/users/roles",
          requiredPermissions: ["Read Roles", "Read Permissions"],
        },
      ],
    },
    {
      label: "Магазин",
      icon: Package,
      children: [
        {
          label: "Продукти",
          icon: List,
          to: "/products/list",
          requiredPermissions: ["Read Products"],
        },
        {
          label: "Атрибути",
          icon: SlidersHorizontal,
          to: "/products/attributes",
          requiredPermissions: ["Read Attributes"],
        },
        {
          label: "Категорії",
          icon: FolderTree,
          to: "/products/categories",
          requiredPermissions: ["Read Product Categories"],
        },
        {
          label: "Бренди",
          icon: Tag,
          to: "/products/brands",
          requiredPermissions: ["Read Product Brands"],
        },
      ],
    },
    {
      label: "Оплата та Доставка",
      icon: Truck,
      to: "/delivery-payment",
      requiredPermissions: ["Read Delivery Methods", "Read Payment Methods"],
    },
    {
      label: "Файли",
      icon: Folder,
      to: "/files",
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
        label: authStore.user?.name || "User",
        slot: "account",
        disabled: true,
      },
    ],
    [
      {
        label: "Профіль",
        icon: User,
        to: "/profile",
      },
      {
        label: "Налаштування",
        icon: Settings,
        to: "/settings",
      },
    ],
    [
      {
        label: "Вихід",
        icon: LogOut,
        onClick: handleLogout,
      },
    ],
  ];
});
</script>

<style scoped></style>
