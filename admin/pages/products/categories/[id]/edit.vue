<template>
  <VSidebarContent title="Редагувати категорію">
    <template #toolbar>
      <div class="flex items-center gap-2 w-full">
        <UButton
          to="/products/categories"
          variant="ghost"
          color="neutral"
        >
          <template #leading>
            <ArrowLeft class="w-5 h-5" />
          </template>
          Повернутись до категорій
        </UButton>
      </div>
    </template>

    <VProductCategoryForm
      v-if="category"
      :category="category"
      :all-categories="flatCategories"
      @cancel="router.push('/products/categories')"
      @success="router.push('/products/categories')"
    />
    <div v-else class="flex items-center justify-center p-8">
      <USpinner />
    </div>
  </VSidebarContent>
</template>

<script setup lang="ts">
import { ArrowLeft } from "lucide-vue-next";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VProductCategoryForm from "~/components/products/categories/forms/VProductCategoryForm.vue";
import type { ProductCategory } from "~/models/productCategory";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Update Product Category"],
});

const router = useRouter();
const route = useRoute();
const productCategoryStore = useProductCategoryStore();

const categoryId = parseInt(route.params.id as string);

const { data: categoriesData } = await productCategoryStore.fetchProductCategories();
const { data: categoryData } = await productCategoryStore.fetchProductCategoryById(categoryId);

const category = computed(() => categoryData.value);

const flatCategories = computed(() => {
  const flatten = (cats: ProductCategory[]): ProductCategory[] => {
    return cats.reduce((acc: ProductCategory[], cat) => {
      // Exclude current category and its descendants
      if (cat.id !== categoryId) {
        acc.push(cat);
        if (cat.subcategories && cat.subcategories.length > 0) {
          acc.push(...flatten(cat.subcategories));
        }
      }
      return acc;
    }, []);
  };
  return flatten(categoriesData.value || []);
});
</script>
