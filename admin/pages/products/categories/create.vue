<template>
  <VSidebarContent title="Створити категорію">
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
      :category="null"
      :all-categories="flatCategories"
      :initial-parent-id="initialParentId"
      @cancel="router.push('/products/categories')"
      @success="router.push('/products/categories')"
    />
  </VSidebarContent>
</template>

<script setup lang="ts">
import { ArrowLeft } from "lucide-vue-next";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VProductCategoryForm from "~/components/products/categories/forms/VProductCategoryForm.vue";
import type { ProductCategory } from "~/models/productCategory";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Create Product Category"],
});

const router = useRouter();
const route = useRoute();
const productCategoryStore = useProductCategoryStore();

// Read parent_id from query params
const initialParentId = computed(() => {
  const parentId = route.query.parent_id;
  return parentId ? parseInt(parentId as string) : null;
});

const { data: categoriesData } = await productCategoryStore.fetchProductCategories();

const flatCategories = computed(() => {
  const flatten = (cats: ProductCategory[]): ProductCategory[] => {
    return cats.reduce((acc: ProductCategory[], cat) => {
      acc.push(cat);
      if (cat.subcategories && cat.subcategories.length > 0) {
        acc.push(...flatten(cat.subcategories));
      }
      return acc;
    }, []);
  };
  return flatten(categoriesData.value || []);
});
</script>
