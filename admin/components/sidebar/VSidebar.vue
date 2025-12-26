<template>
  <UDashboardSidebar
    collapsible
    :ui="{ footer: 'border-t border-default' }"
    resizable
  >
    <template #header="{ collapsed }">
      <div v-if="!collapsed" class="flex items-center justify-between">
        <div class="flex items-center gap-2 ">
          <img src="@/assets/images/logo.png" alt="logo" class="h-7 w-auto shrink-0 rounded" />
          Admin iD
        </div>
      </div>
      <div v-else class="flex flex-col items-center gap-2 w-full">
        <img src="@/assets/images/logo.png" alt="logo" class="h-7 w-auto shrink-0 rounded" />
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
  Newspaper,
  FileText,
  MessageSquare,
  Store,
  PhoneCall,
  Cog,
} from "lucide-vue-next";
import type { Component } from "vue";
import VAvatar from "@/components/common/VAvatar.vue";


const { t } = useI18n();
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
      label: t("nav.main"),
      icon: Home,
      to: "/",
    },
    {
      label: t("nav.users"),
      icon: Users,
      children: [
        {
          label: t("nav.administrators"),
          icon: ShieldCheck,
          to: "/users/administrators",
          requiredPermissions: ["Read Admin Users"],
        },
        {
          label: t("nav.customers"),
          icon: ShoppingBag,
          to: "/users/customers",
          requiredPermissions: ["Read Customers"],
        },
        {
          label: t("nav.roles"),
          icon: ContactRound,
          to: "/users/roles",
          requiredPermissions: ["Read Roles", "Read Permissions"],
        },
      ],
    },
    {
      label: t("nav.shop"),
      icon: Package,
      children: [
        {
          label: t("nav.products"),
          icon: List,
          to: "/products/list",
          requiredPermissions: ["Read Products"],
        },
        {
          label: t("nav.attributes"),
          icon: SlidersHorizontal,
          to: "/products/attributes",
          requiredPermissions: ["Read Attributes"],
        },
        {
          label: t("nav.categories"),
          icon: FolderTree,
          to: "/products/categories",
          requiredPermissions: ["Read Product Categories"],
        },
        {
          label: t("nav.brands"),
          icon: Tag,
          to: "/products/brands",
          requiredPermissions: ["Read Product Brands"],
        },
        {
          label: t("nav.reviews"),
          icon: MessageSquare,
          to: "/reviews",
          requiredPermissions: ["Read Product Reviews"],
        },
      ],
    },
    {
      label: t("nav.blog"),
      icon: Newspaper,
      children: [
        {
          label: t("nav.categories"),
          icon: FolderTree,
          to: "/blog/categories",
          requiredPermissions: ["Read Blog Categories"],
        },
        {
          label: t("nav.posts"),
          icon: FileText,
          to: "/blog/posts",
          requiredPermissions: ["Read Blog Posts"],
        },
      ],
    },
    {
      label: t("nav.deliveryPayment"),
      icon: Truck,
      to: "/delivery-payment",
      requiredPermissions: ["Read Delivery Methods", "Read Payment Methods"],
    },
    {
      label: t("nav.files"),
      icon: Folder,
      to: "/files",
    },
    {
      label: t("nav.storeSettings"),
      icon: Store,
      to: "/store-settings",
      requiredPermissions: ["Read Store Settings"],
    },
    {
      label: t("nav.callbackRequests"),
      icon: PhoneCall,
      to: "/callback-requests",
      requiredPermissions: ["Read Callback Requests"],
    },
    {
      label: t("nav.systemSettings"),
      icon: Cog,
      to: "/system-settings",
      requiredPermissions: ["Read System Settings"],
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
        label: t("nav.profile"),
        icon: User,
        to: "/profile",
      },
      {
        label: t("nav.settings"),
        icon: Settings,
        to: "/settings",
      },
    ],
    [
      {
        label: t("nav.logout"),
        icon: LogOut,
        onClick: handleLogout,
      },
    ],
  ];
});
</script>

<style scoped></style>
