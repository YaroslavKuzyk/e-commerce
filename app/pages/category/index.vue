<template>
  <UPage>
    <UContainer>
      <div class="py-6">
        <h1 class="text-3xl font-bold mb-6">Каталог товарів</h1>

        <div v-if="isLoading" class="flex justify-center py-12">
          <UButton loading variant="ghost" />
        </div>

        <div v-else-if="categories.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <NuxtLink
            v-for="category in categories"
            :key="category.id"
            :to="`/category/${category.slug}`"
            class="group flex flex-col items-center gap-4 p-6 rounded-xl bg-muted/50 hover:bg-muted transition-colors"
          >
            <div class="w-24 h-24 flex items-center justify-center">
              <VSecureImage
                v-if="category.logo_file_id"
                :fileId="category.logo_file_id"
                imgClass="max-w-full max-h-full object-contain"
              />
              <div v-else class="w-20 h-20 bg-gray-100 rounded-lg flex items-center justify-center">
                <Folder class="w-10 h-10 text-gray-400" />
              </div>
            </div>
            <div class="text-center">
              <h3 class="font-semibold group-hover:text-primary transition-colors">
                {{ category.name }}
              </h3>
              <p class="text-sm text-dimmed mt-1">
                {{ category.products_count }} товарів
              </p>
            </div>
          </NuxtLink>
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
import VSecureImage from "~/components/common/VSecureImage.vue";
import type { FilterCategory } from "~/models/product";

const client = useSanctumClient();
const isLoading = ref(true);
const categories = ref<FilterCategory[]>([]);

const fetchCategories = async () => {
  isLoading.value = true;
  try {
    const response = await client<{
      success: boolean;
      data: FilterCategory[];
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
