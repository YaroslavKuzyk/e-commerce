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
          <template v-for="(phone, index) in phones" :key="index">
            <!-- text: показує тільки номер як текст -->
            <span
              v-if="phone.display_type === 'text'"
              class="text-sm text-muted"
            >
              {{ phone.number }}
            </span>

            <!-- button: кнопка з міткою без посилання -->
            <UButton
              v-else-if="phone.display_type === 'button'"
              variant="soft"
              size="xs"
              color="neutral"
            >
              {{ phone.label }}
            </UButton>

            <!-- button-link: кнопка з міткою та посиланням tel: -->
            <UButton
              v-else-if="phone.display_type === 'button-link'"
              variant="soft"
              size="xs"
              color="neutral"
              :to="`tel:${phone.number.replace(/[^+\d]/g, '')}`"
            >
              {{ phone.label }}
            </UButton>

            <!-- link: текстове посилання з номером -->
            <a
              v-else-if="phone.display_type === 'link'"
              :href="`tel:${phone.number.replace(/[^+\d]/g, '')}`"
              class="text-sm text-muted hover:text-primary transition-colors"
            >
              {{ phone.number }}
            </a>

            <!-- fallback для старих даних без display_type -->
            <span v-else class="text-sm text-muted">
              {{ phone.number }}
            </span>
          </template>

          <template v-if="phones.length === 0">
            <UButton variant="soft" size="xs" color="neutral">{{
              $t("header.telegram")
            }}</UButton>
            <UButton variant="soft" size="xs" color="neutral">{{
              $t("header.viber")
            }}</UButton>
            <span class="text-sm text-muted">+38 (099) 028-41-95</span>
          </template>

          <UButton variant="soft" size="xs" color="neutral" @click="isCallbackModalOpen = true">{{
            $t("header.callBack")
          }}</UButton>
        </div>
      </div>
      <div class="flex items-center gap-4">
        <NuxtLink to="/">
          <VSecureImage
            v-if="logoFileId"
            :file-id="logoFileId"
            alt="Logo"
            width="w-auto"
            object-fit="contain"
            img-class="h-[50px]"
          />
          <IconLogo v-else />
        </NuxtLink>

        <UButton @click="isCatalogModalOpen = true">
          <template #leading>
            <Menu class="w-5 h-5" />
          </template>
          {{ $t("common.catalog") }}
        </UButton>

        <VGlobalSearch />

        <div class="flex gap-4">
          <UButton variant="ghost" color="neutral">
            <template #leading>
              <UserRound class="w-5 h-5" />
            </template>
          </UButton>
          <NuxtLink :to="localePath('/favorites')">
            <UButton variant="ghost" color="neutral" class="relative">
              <template #leading>
                <Heart class="w-5 h-5" />
              </template>
              <span
                v-if="favoriteStore.count > 0"
                class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-medium"
              >
                {{ favoriteStore.count > 99 ? '99+' : favoriteStore.count }}
              </span>
            </UButton>
          </NuxtLink>
          <UButton variant="ghost" color="neutral">
            <template #leading>
              <Scale class="w-5 h-5" />
            </template>
          </UButton>
          <NuxtLink :to="localePath('/cart')">
            <UButton variant="ghost" color="neutral" class="relative">
              <template #leading>
                <ShoppingCart class="w-5 h-5" />
              </template>
              <span
                v-if="cartStore.totalQuantity > 0"
                class="absolute -top-1 -right-1 w-5 h-5 bg-primary text-white text-xs rounded-full flex items-center justify-center font-medium"
              >
                {{ cartStore.totalQuantity > 99 ? '99+' : cartStore.totalQuantity }}
              </span>
            </UButton>
          </NuxtLink>
        </div>
      </div>
    </UContainer>

    <VCatalogModal v-model="isCatalogModalOpen" />
    <VCallbackModal v-model="isCallbackModalOpen" />
  </header>
</template>

<script lang="ts" setup>
import VLangDropdown from "~/components/common/VLangDropdown.vue";
import VGlobalSearch from "~/components/common/VGlobalSearch.vue";
import VSecureImage from "~/components/common/VSecureImage.vue";
import IconLogo from "~/components/icons/IconLogo.vue";
import VCatalogModal from "~/components/category/modals/VCategoryModal.vue";
import VCallbackModal from "~/components/common/modals/VCallbackModal.vue";
import {
  Menu,
  UserRound,
  Heart,
  Scale,
  ShoppingCart,
  ChevronDown,
  MapPin,
} from "lucide-vue-next";
import { useFavoriteStore } from "~/stores/useFavoriteStore";
import { useCartStore } from "~/stores/useCartStore";

const isCatalogModalOpen = ref(false);
const isCallbackModalOpen = ref(false);

const { t } = useI18n();
const localePath = useLocalePath();
const { phones, logoFileId } = useStoreSettings();
const favoriteStore = useFavoriteStore();
const cartStore = useCartStore();

// Initialize favorites and cart on mount
onMounted(() => {
  favoriteStore.init();
  cartStore.init();
});

const menuItems = computed(() => {
  return [
    {
      label: t("nav.store"),
      to: localePath("/store"),
    },
    {
      label: t("nav.categories"),
      to: localePath("/category"),
    },
    {
      label: t("nav.promotions"),
      to: localePath("/store/akcii"),
    },
    {
      label: t("nav.discount"),
      to: localePath("/store/ucinka"),
    },
    {
      label: t("nav.blog"),
      to: localePath("/blog"),
    },
  ];
});
</script>
