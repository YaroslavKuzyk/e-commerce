<template>
  <header>
    <UContainer>
      <div class="flex items-center justify-between gap-4 py-2">
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2">
            <VLangDropdown />
            <UButton color="neutral" variant="ghost" class="gap-1">
              <template #leading>
                <MapPin size="16" />
              </template>
              <span>{{ $t("header.city") }}</span>
              <ChevronDown size="16" />
            </UButton>
          </div>
          <div class="h-5 w-[1px] bg-neutral-200"></div>
          <UNavigationMenu :items="menuItems" />
        </div>
        <div class="flex items-center gap-4">
          <UButton variant="soft" size="xs" color="neutral">{{
            $t("header.telegram")
          }}</UButton>
          <UButton variant="soft" size="xs" color="neutral">{{
            $t("header.viber")
          }}</UButton>
          <UNavigationMenu :items="phoneItems" />
          <UButton variant="soft" size="xs" color="neutral">{{
            $t("header.callBack")
          }}</UButton>
        </div>
      </div>
      <div class="flex items-center gap-4">
        <IconLogo />

        <UButton @click="isCatalogModalOpen = true">
          <template #leading>
            <Menu class="w-5 h-5" />
          </template>
          {{ $t("common.catalog") }}
        </UButton>

        <div class="flex-1 flex">
          <UInput
            :placeholder="$t('common.searchPlaceholder')"
            class="flex-1"
            :ui="{
              base: 'rounded-e-none',
            }"
          >
            <template #leading>
              <Search class="w-5 h-5 text-neutral-400" />
            </template>
          </UInput>
          <UButton color="neutral" class="rounded-s-none">{{
            $t("common.search")
          }}</UButton>
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
    </UContainer>

    <VCatalogModal v-model="isCatalogModalOpen" />
  </header>
</template>

<script lang="ts" setup>
import VLangDropdown from "~/components/common/VLangDropdown.vue";
import IconLogo from "~/components/icons/IconLogo.vue";
import VCatalogModal from "~/components/category/modals/VCategoryModal.vue";
import {
  Menu,
  Search,
  UserRound,
  Heart,
  Scale,
  ShoppingCart,
  ChevronDown,
  MapPin,
} from "lucide-vue-next";

const isCatalogModalOpen = ref(false);

const { t } = useI18n();
const localePath = useLocalePath();
const { phoneOne, phoneTwo } = useConstants();

const menuItems = computed(() => {
  return [
    {
      label: t("nav.store"),
      to: localePath("/store"),
    },
    {
      label: t("nav.categories"),
      to: localePath("/categories"),
    },
    {
      label: t("nav.promotions"),
      to: localePath("/promotions"),
    },
    {
      label: t("nav.discount"),
      to: localePath("/discount"),
    },
    {
      label: t("nav.blog"),
      to: localePath("/blog"),
    },
    {
      label: t("nav.contacts"),
      to: localePath("/contacts"),
    },
  ];
});

const phoneItems = computed(() => {
  return [
    {
      label: phoneOne,
    },
    {
      label: phoneTwo,
    },
  ];
});
</script>
