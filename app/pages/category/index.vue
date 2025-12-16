<template>
  <UPage>
    <UContainer>
      <div class="py-6">
        <h1 class="text-3xl font-bold mb-6">Каталог товарів</h1>

        <div v-if="isLoading" class="flex justify-center py-12">
          <UButton loading variant="ghost" />
        </div>

        <div v-else-if="categories.length > 0" class="flex gap-6 flex-wrap">
          <VCategoryThumb
            v-for="category in categories"
            :key="category.id"
            :item="category"
            class="max-w-[50%] flex-1"
          />
        </div>

        <div v-else class="text-center py-12">
          <Folder class="w-16 h-16 text-gray-400 mx-auto mb-4" />
          <h3 class="text-xl font-semibold mb-2">Категорій не знайдено</h3>
          <p class="text-dimmed">Поки що немає доступних категорій</p>
        </div>
      </div>
    </UContainer>
  </UPage>
</template>

<script setup lang="ts">
import { Folder } from "lucide-vue-next";
import VCategoryThumb from "~/components/category/list/VCategoryThumb.vue";
import type { ProductCategory } from "~/models/productCategory";

const client = useSanctumClient();
const isLoading = ref(true);
const categories = ref<ProductCategory[]>([]);

const fetchCategories = async () => {
  isLoading.value = true;
  try {
    const response = await client<{
      success: boolean;
      data: ProductCategory[];
    }>('/api/product-categories');

    categories.value = response.data;
  } catch (e) {
    console.error('Failed to fetch categories:', e);
  } finally {
    isLoading.value = false;
  }
};

await fetchCategories();

useHead({
  title: 'Каталог товарів',
});
</script>
