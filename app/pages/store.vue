<template>
  <UPage>
    <UContainer>
      <div class="py-6">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-3xl font-bold mb-2">Магазин</h1>
          <p class="text-dimmed">Всі товари</p>
        </div>

        <!-- Products Grid -->
        <div class="flex gap-6 flex-wrap">
          <VProductThumb
            class="w-[calc(25%-18px)]"
            v-for="product in products"
            :key="product.id"
            :product="product"
          />
        </div>

        <!-- Empty State -->
        <div v-if="products && products.length === 0" class="text-center py-12">
          <Package class="w-16 h-16 text-gray-400 mx-auto mb-4" />
          <h3 class="text-xl font-semibold mb-2">Товарів не знайдено</h3>
          <p class="text-dimmed">Спробуйте змінити параметри пошуку</p>
        </div>

        <!-- Pagination -->
        <div v-if="meta && meta.last_page > 1" class="flex justify-center mt-8">
          <UPagination
            v-model="currentPage"
            :page-count="meta.per_page"
            :total="meta.total"
            :ui="{ base: 'gap-1' }"
          />
        </div>
      </div>
    </UContainer>
  </UPage>
</template>

<script setup lang="ts">
import { Package } from "lucide-vue-next";
import VProductThumb from "~/components/product/list/VProductThumb.vue";
import type { Product } from "~/models/product";

const route = useRoute();
const router = useRouter();

const currentPage = ref(Number(route.query.page) || 1);
const limit = 12;

const products = ref<Product[]>([]);
const meta = ref<{
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
} | null>(null);

const fetchProducts = async () => {
  const client = useSanctumClient();

  const response = await client<{
    success: boolean;
    data: Product[];
    meta: {
      current_page: number;
      last_page: number;
      per_page: number;
      total: number;
    };
  }>(`/api/products?page=${currentPage.value}&limit=${limit}`);

  products.value = response.data;
  meta.value = response.meta;
};

// Initial fetch
await fetchProducts();

// Watch for page changes
watch(currentPage, async (newPage) => {
  router.push({ query: { ...route.query, page: newPage } });
  await fetchProducts();
});

// Watch route query for browser back/forward
watch(
  () => route.query.page,
  (newPage) => {
    const page = Number(newPage) || 1;
    if (page !== currentPage.value) {
      currentPage.value = page;
    }
  }
);

useHead({
  title: "Магазин - Всі товари",
});
</script>
