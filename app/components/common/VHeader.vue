<template>
  <header class="px-6">
    <div class="flex items-center justify-between gap-4 py-2">
      <div class="flex items-center gap-2">
        <VLangDropdown />
        <div>Київ</div>
      </div>
      <div class="separator"></div>
      <UNavigationMenu :items="menuItems" />
      <div class="separator"></div>
      <div class="flex items-center gap-4">
        <UButton variant="soft" size="xs" color="neutral">Telegram</UButton>
        <UButton variant="soft" size="xs" color="neutral">Viber</UButton>
        <UNavigationMenu :items="phoneItems" />
        <UButton variant="soft" size="xs" color="neutral">Передзвоніть</UButton>
      </div>
    </div>
    <div class="flex items-center gap-6">
      <IconLogo />

      <UButton>
        <template #leading>
          <Menu class="w-5 h-5" />
        </template>
        Каталог
      </UButton>

      <div class="flex-1 flex">
        <UInput
          placeholder="Я хочу знайти..."
          class="flex-1"
          :ui="{
            base: 'rounded-e-none',
          }"
        >
          <template #leading>
            <Search class="w-5 h-5 text-neutral-400" />
          </template>
        </UInput>
        <UButton color="neutral" class="rounded-s-none">Пошук</UButton>
      </div>

      <div class="flex gap-4">
        <UButton variant="ghost" color="neutral">
          <template #leading>
            <UserRound class="w-5 h-5" />
          </template>
        </UButton>
        <UButton variant="ghost" color="neutral">
          <template #leading>
            <Heart class="w-5 h-5" />
          </template>
        </UButton>
        <UButton variant="ghost" color="neutral">
          <template #leading>
            <Scale class="w-5 h-5" />
          </template>
        </UButton>
        <UButton variant="ghost" color="neutral">
          <template #leading>
            <ShoppingCart class="w-5 h-5" />
          </template>
        </UButton>
      </div>
    </div>
  </header>
</template>

<script lang="ts" setup>
import VLangDropdown from "~/components/common/VLangDropdown.vue";
import IconLogo from "~/components/icons/IconLogo.vue";
import {
  Menu,
  Search,
  UserRound,
  Heart,
  Scale,
  ShoppingCart,
} from "lucide-vue-next";

const menuItems = computed(() => {
  return [
    {
      label: "Магазин",
      to: "/store",
    },
    {
      label: "Категорії",
      to: "/categories",
    },
    {
      label: "Акції",
      to: "/actions",
    },
    {
      label: "Уцінка",
      to: "/discount",
    },
    {
      label: "Блог",
      to: "/blog",
    },
    {
      label: "Контакти",
      to: "/contacts",
    },
  ];
});

const phoneItems = computed(() => {
  return [
    {
      label: "+38 (099) 028-41-95",
    },
    {
      label: "+38 (099) 028-41-95",
    },
  ];
});
</script>

<style lang="scss" scoped>
.separator {
  display: flex;
  width: 1px;
  height: 20px;
  background-color: var(--ui-border);
}
</style>
