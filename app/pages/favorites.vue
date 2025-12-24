<template>
  <UContainer class="py-8">
    <!-- Breadcrumbs -->
    <div class="mb-6">
      <nav class="flex items-center gap-2 text-sm text-gray-500">
        <NuxtLink to="/" class="hover:text-primary">{{ $t("common.home") }}</NuxtLink>
        <ChevronRight class="w-4 h-4" />
        <span class="text-gray-900 dark:text-white">{{ $t("favorites.title") }}</span>
      </nav>
    </div>

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center gap-3">
        <div class="w-1 h-8 bg-primary rounded-full"></div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ $t("favorites.title") }}
        </h1>
        <span v-if="favoriteStore.count > 0" class="text-gray-500">
          ({{ favoriteStore.count }})
        </span>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center py-16">
      <div class="flex flex-col items-center gap-3">
        <UButton loading variant="ghost" size="xl" />
        <span class="text-sm text-gray-500">{{ $t("favorites.loading") }}</span>
      </div>
    </div>

    <!-- Guest State - Show products from localStorage -->
    <template v-else-if="!isAuthenticated && favoriteStore.count > 0">
      <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
        <p class="text-sm text-blue-700 dark:text-blue-300">
          {{ $t("favorites.guestNotice") }}
        </p>
      </div>

      <!-- Products Grid for Guest -->
      <div v-if="guestProducts.length > 0">
        <VProductsGrid :products="guestProducts" :is-loading="isLoadingGuest" :columns="4" />
      </div>
      <div v-else-if="!isLoadingGuest" class="text-center py-8 text-gray-500">
        {{ $t("favorites.loadingProducts") }}
      </div>
    </template>

    <!-- Auth State - Show products from server -->
    <template v-else-if="isAuthenticated && products.length > 0">
      <VProductsGrid :products="products" :is-loading="false" :columns="4" />

      <!-- Pagination -->
      <div v-if="meta && meta.last_page > 1" class="flex justify-center mt-8">
        <UPagination
          v-model="currentPage"
          :page-count="meta.per_page"
          :total="meta.total"
        />
      </div>
    </template>

    <!-- Empty State -->
    <div v-else class="text-center py-16">
      <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
        <Heart class="w-12 h-12 text-gray-400" />
      </div>
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
        {{ $t("favorites.emptyTitle") }}
      </h2>
      <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
        {{ $t("favorites.emptyDescription") }}
      </p>
      <NuxtLink :to="localePath('/store')">
        <UButton size="lg">
          <template #leading>
            <ShoppingBag class="w-5 h-5" />
          </template>
          {{ $t("favorites.goToStore") }}
        </UButton>
      </NuxtLink>
    </div>
  </UContainer>
</template>

<script lang="ts" setup>
import { Heart, ChevronRight, ShoppingBag } from "lucide-vue-next";
import { useFavoriteStore } from "~/stores/useFavoriteStore";
import type { Product } from "~/models/product";
import VProductsGrid from "~/components/product/list/VProductsGrid.vue";

const { t } = useI18n();
const localePath = useLocalePath();
const favoriteStore = useFavoriteStore();
const client = useSanctumClient();
const { user } = useSanctumAuth();

const isAuthenticated = computed(() => !!user.value);
const isLoading = ref(true);
const isLoadingGuest = ref(false);
const products = ref<Product[]>([]);
const guestProducts = ref<Product[]>([]);
const meta = ref<{ current_page: number; last_page: number; per_page: number; total: number } | null>(null);
const currentPage = ref(1);

// Initialize favorites
onMounted(async () => {
  await favoriteStore.init();

  if (isAuthenticated.value) {
    await loadAuthFavorites();
  } else {
    await loadGuestFavorites();
  }

  isLoading.value = false;
});

// Load favorites for authenticated users
const loadAuthFavorites = async () => {
  const result = await favoriteStore.getFavoriteProducts(currentPage.value);
  products.value = result.data;
  meta.value = result.meta;
};

// Load favorites for guest users (by IDs from localStorage)
const loadGuestFavorites = async () => {
  const ids = Array.from(favoriteStore.favoriteIds);
  if (ids.length === 0) return;

  isLoadingGuest.value = true;

  try {
    // Fetch products by IDs
    const slugsParam = ids.join(",");
    const response = await client<{ success: boolean; data: Product[] }>(
      `/api/products?ids=${slugsParam}&limit=50`
    );
    guestProducts.value = response.data || [];
  } catch (error) {
    console.error("Failed to load guest favorites:", error);
  } finally {
    isLoadingGuest.value = false;
  }
};

// Watch page changes
watch(currentPage, async () => {
  if (isAuthenticated.value) {
    isLoading.value = true;
    await loadAuthFavorites();
    isLoading.value = false;
  }
});

// Watch auth state changes
watch(isAuthenticated, async (newValue) => {
  isLoading.value = true;

  if (newValue) {
    // User logged in - sync and reload
    await favoriteStore.syncWithServer();
    await loadAuthFavorites();
  } else {
    // User logged out - clear and reload from storage
    favoriteStore.clear();
    await favoriteStore.init();
    await loadGuestFavorites();
  }

  isLoading.value = false;
});

// SEO
useHead({
  title: t("favorites.title"),
});
</script>
