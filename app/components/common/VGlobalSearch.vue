<template>
  <div class="relative flex-1">
    <div class="flex">
      <UInput
        v-model="searchQuery"
        :placeholder="$t('common.searchPlaceholder')"
        class="flex-1"
        :ui="{
          base: 'rounded-e-none',
        }"
        @focus="showResults = true"
        @input="onInput"
      >
        <template #leading>
          <Search class="w-5 h-5 text-neutral-400" />
        </template>
      </UInput>
      <UButton color="neutral" class="rounded-s-none" @click="onSearchClick">
        {{ $t("common.search") }}
      </UButton>
    </div>

    <!-- Search Results Dropdown -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-1"
    >
      <div
        v-if="showResults && searchQuery.length >= 2"
        class="absolute top-full left-0 right-0 mt-1 bg-default rounded-lg shadow-xl border border-default z-50 max-h-[500px] overflow-y-auto"
        @mousedown.prevent
      >
        <!-- Loading State -->
        <div v-if="isLoading" class="p-4 text-center">
          <UButton loading variant="ghost" />
        </div>

        <!-- No Results -->
        <div v-else-if="hasNoResults" class="p-4 text-center text-dimmed">
          {{ $t("common.noResults") }}
        </div>

        <!-- Results -->
        <div v-else>
          <!-- Products -->
          <div v-if="results.products.length > 0" class="p-2">
            <div class="px-2 py-1 text-xs font-semibold text-dimmed uppercase">
              {{ $t("common.products") }}
            </div>
            <NuxtLink
              v-for="product in results.products"
              :key="`product-${product.id}`"
              :to="`/product/${product.slug}`"
              class="flex items-center gap-3 p-2 rounded-md hover:bg-elevated transition"
              @click="closeResults"
            >
              <VSecureImage
                v-if="product.main_image_file_id"
                :fileId="product.main_image_file_id"
                imgClass="w-10 h-10 rounded object-cover"
              />
              <div v-else class="w-10 h-10 rounded bg-muted flex items-center justify-center">
                <Package class="w-5 h-5 text-dimmed" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate">{{ product.name }}</p>
                <p v-if="product.category" class="text-xs text-dimmed truncate">
                  {{ product.category.name }}
                </p>
              </div>
              <div class="text-sm font-semibold">
                {{ formatPrice(product.price) }}
              </div>
            </NuxtLink>
          </div>

          <!-- Categories -->
          <div v-if="results.categories.length > 0" class="p-2 border-t border-default">
            <div class="px-2 py-1 text-xs font-semibold text-dimmed uppercase">
              {{ $t("common.categories") }}
            </div>
            <NuxtLink
              v-for="category in results.categories"
              :key="`category-${category.id}`"
              :to="getCategoryUrl(category)"
              class="flex items-center gap-3 p-2 rounded-md hover:bg-elevated transition"
              @click="closeResults"
            >
              <div class="w-10 h-10 rounded bg-muted flex items-center justify-center">
                <Folder class="w-5 h-5 text-dimmed" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate">{{ category.name }}</p>
                <p v-if="category.parent" class="text-xs text-dimmed truncate">
                  {{ category.parent.name }}
                </p>
                <p v-else-if="category.subtitle" class="text-xs text-dimmed truncate">
                  {{ category.subtitle }}
                </p>
              </div>
              <ChevronRight class="w-4 h-4 text-dimmed" />
            </NuxtLink>
          </div>

          <!-- Blog Categories -->
          <div v-if="results.blog_categories.length > 0" class="p-2 border-t border-default">
            <div class="px-2 py-1 text-xs font-semibold text-dimmed uppercase">
              {{ $t("common.blogCategories") }}
            </div>
            <NuxtLink
              v-for="category in results.blog_categories"
              :key="`blog-category-${category.id}`"
              :to="`/blog/${category.slug}`"
              class="flex items-center gap-3 p-2 rounded-md hover:bg-elevated transition"
              @click="closeResults"
            >
              <div class="w-10 h-10 rounded bg-muted flex items-center justify-center">
                <BookOpen class="w-5 h-5 text-dimmed" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate">{{ category.name }}</p>
                <p v-if="category.description" class="text-xs text-dimmed truncate">
                  {{ category.description }}
                </p>
              </div>
              <ChevronRight class="w-4 h-4 text-dimmed" />
            </NuxtLink>
          </div>

          <!-- Blog Posts -->
          <div v-if="results.blog_posts.length > 0" class="p-2 border-t border-default">
            <div class="px-2 py-1 text-xs font-semibold text-dimmed uppercase">
              {{ $t("common.blogPosts") }}
            </div>
            <NuxtLink
              v-for="post in results.blog_posts"
              :key="`blog-post-${post.id}`"
              :to="`/blog/${post.category?.slug || 'all'}/${post.slug}`"
              class="flex items-center gap-3 p-2 rounded-md hover:bg-elevated transition"
              @click="closeResults"
            >
              <VSecureImage
                v-if="post.preview_image_id"
                :fileId="post.preview_image_id"
                imgClass="w-10 h-10 rounded object-cover"
              />
              <div v-else class="w-10 h-10 rounded bg-muted flex items-center justify-center">
                <FileText class="w-5 h-5 text-dimmed" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate">{{ post.title }}</p>
                <p v-if="post.category" class="text-xs text-dimmed truncate">
                  {{ post.category.name }}
                </p>
              </div>
              <ChevronRight class="w-4 h-4 text-dimmed" />
            </NuxtLink>
          </div>

          <!-- View All Results -->
          <div v-if="hasResults" class="p-2 border-t border-default">
            <UButton
              variant="ghost"
              color="neutral"
              block
              @click="goToFullSearch"
            >
              {{ $t("common.viewAllResults") }}
            </UButton>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Backdrop -->
    <div
      v-if="showResults && searchQuery.length >= 2"
      class="fixed inset-0 z-40"
      @click="closeResults"
    />
  </div>
</template>

<script setup lang="ts">
import { Search, Package, Folder, ChevronRight, BookOpen, FileText } from "lucide-vue-next";
import VSecureImage from "~/components/common/VSecureImage.vue";

interface SearchProduct {
  id: number;
  name: string;
  slug: string;
  price: number;
  base_price: number;
  main_image_file_id: number | null;
  category: { name: string; slug: string } | null;
  brand: { name: string; slug: string } | null;
}

interface SearchCategory {
  id: number;
  name: string;
  slug: string;
  subtitle: string | null;
  logo_file_id: number | null;
  is_subcategory: boolean;
  parent: { name: string; slug: string } | null;
}

interface SearchBlogCategory {
  id: number;
  name: string;
  slug: string;
  description: string | null;
}

interface SearchBlogPost {
  id: number;
  title: string;
  slug: string;
  short_description: string | null;
  preview_image_id: number | null;
  publication_date: string | null;
  category: { name: string; slug: string } | null;
}

interface SearchResults {
  products: SearchProduct[];
  categories: SearchCategory[];
  blog_categories: SearchBlogCategory[];
  blog_posts: SearchBlogPost[];
}

const config = useRuntimeConfig();
const router = useRouter();
const apiBase = config.public.sanctum?.baseUrl ;

const searchQuery = ref("");
const showResults = ref(false);
const isLoading = ref(false);
const results = ref<SearchResults>({
  products: [],
  categories: [],
  blog_categories: [],
  blog_posts: [],
});

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const hasResults = computed(() => {
  return (
    results.value.products.length > 0 ||
    results.value.categories.length > 0 ||
    results.value.blog_categories.length > 0 ||
    results.value.blog_posts.length > 0
  );
});

const hasNoResults = computed(() => {
  return searchQuery.value.length >= 2 && !isLoading.value && !hasResults.value;
});

const formatPrice = (price: number) => {
  return new Intl.NumberFormat("uk-UA", {
    style: "currency",
    currency: "UAH",
    minimumFractionDigits: 0,
  }).format(price);
};

const getCategoryUrl = (category: SearchCategory) => {
  if (category.parent) {
    return `/category/${category.parent.slug}/${category.slug}`;
  }
  return `/category/${category.slug}`;
};

const performSearch = async () => {
  if (searchQuery.value.length < 2) {
    results.value = {
      products: [],
      categories: [],
      blog_categories: [],
      blog_posts: [],
    };
    return;
  }

  isLoading.value = true;

  try {
    const response = await $fetch<{ success: boolean; data: SearchResults }>(
      `${apiBase}/api/search`,
      {
        params: { q: searchQuery.value, limit: 5 },
      }
    );

    results.value = response.data;
  } catch (e) {
    console.error("Search failed:", e);
    results.value = {
      products: [],
      categories: [],
      blog_categories: [],
      blog_posts: [],
    };
  } finally {
    isLoading.value = false;
  }
};

const onInput = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }

  searchTimeout = setTimeout(() => {
    performSearch();
  }, 300);
};

const closeResults = () => {
  showResults.value = false;
};

const onSearchClick = () => {
  if (searchQuery.value.length >= 2) {
    goToFullSearch();
  }
};

const goToFullSearch = () => {
  closeResults();
  router.push(`/store?search=${encodeURIComponent(searchQuery.value)}`);
};

// Close results when clicking outside
onMounted(() => {
  document.addEventListener("click", (e) => {
    const target = e.target as HTMLElement;
    if (!target.closest(".relative.flex-1")) {
      closeResults();
    }
  });
});

onUnmounted(() => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }
});
</script>
