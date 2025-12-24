<template>
  <UContainer class="py-8">
    <!-- Breadcrumbs -->
    <div class="mb-6">
      <nav class="flex items-center gap-2 text-sm text-gray-500">
        <NuxtLink :to="localePath('/')" class="hover:text-primary">
          {{ $t("common.home") }}
        </NuxtLink>
        <ChevronRight class="w-4 h-4" />
        <span class="text-gray-900 dark:text-white">
          {{ $t("comparison.title") }}
        </span>
      </nav>
    </div>

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center gap-3">
        <div class="w-1 h-8 bg-primary rounded-full"></div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ $t("comparison.title") }}
        </h1>
        <span v-if="comparisonStore.totalCount > 0" class="text-gray-500">
          ({{ comparisonStore.totalCount }})
        </span>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center py-16">
      <UButton loading variant="ghost" size="xl" />
    </div>

    <!-- Comparison Lists -->
    <div v-else-if="comparisonLists.length > 0" class="grid gap-4">
      <VComparisonListCard
        v-for="list in comparisonLists"
        :key="list.category.id"
        :list="list"
        @clear="clearCategory(list.category.slug, list.category.id)"
      />
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-16">
      <div
        class="w-24 h-24 mx-auto mb-6 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center"
      >
        <BarChart3 class="w-12 h-12 text-gray-400" />
      </div>
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
        {{ $t("comparison.emptyTitle") }}
      </h2>
      <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
        {{ $t("comparison.emptyDescription") }}
      </p>
      <NuxtLink :to="localePath('/store')">
        <UButton size="lg">
          {{ $t("comparison.goToStore") }}
        </UButton>
      </NuxtLink>
    </div>
  </UContainer>
</template>

<script lang="ts" setup>
import { ChevronRight, BarChart3 } from "lucide-vue-next";
import { useComparisonStore } from "~/stores/useComparisonStore";
import VComparisonListCard from "~/components/comparison/VComparisonListCard.vue";

const { t } = useI18n();
const localePath = useLocalePath();
const comparisonStore = useComparisonStore();

interface ComparisonList {
  category: {
    id: number;
    name: string;
    slug: string;
    logo_file_id?: number | null;
  };
  products_count: number;
  product_ids: number[];
  preview_images: number[];
}

const isLoading = ref(true);
const comparisonLists = ref<ComparisonList[]>([]);

onMounted(async () => {
  await comparisonStore.init();
  await loadLists();
  isLoading.value = false;
});

const loadLists = async () => {
  comparisonLists.value = await comparisonStore.getComparisonLists();
};

const clearCategory = async (slug: string, id: number) => {
  await comparisonStore.clearCategory(slug, id);
  await loadLists();
};

useHead({
  title: t("comparison.title"),
});
</script>
