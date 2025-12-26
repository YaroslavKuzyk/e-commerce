<template>
  <VSidebarContent title="Налаштування меню каталогу">
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

    <div v-if="loading" class="flex items-center justify-center p-8">
      <USpinner />
    </div>

    <div v-else-if="category" class="space-y-6">
      <!-- Category Info -->
      <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <div class="flex items-center gap-3">
          <VSecureImage
            v-if="category.logo_file_id"
            :fileId="category.logo_file_id"
            class="w-10 h-10 rounded"
          />
          <div>
            <h2 class="font-semibold text-lg">{{ category.name }}</h2>
            <p class="text-sm text-gray-500">Налаштуйте меню для цієї категорії</p>
          </div>
        </div>
      </div>

      <!-- Menu Editor -->
      <VCatalogMenuEditor
        v-if="menu"
        :menu="menu"
        @refresh="refreshMenu"
      />

      <div
        v-else
        class="text-center py-8 text-gray-500"
      >
        <UIcon name="i-heroicons-squares-2x2" class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" />
        <p class="mb-4">Меню ще не створено</p>
        <UButton @click="createMenu" :loading="creating">
          Створити меню
        </UButton>
      </div>
    </div>
  </VSidebarContent>
</template>

<script setup lang="ts">
import { ArrowLeft } from "lucide-vue-next";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VSecureImage from "~/components/common/VSecureImage.vue";
import VCatalogMenuEditor from "~/components/catalog-menu/VCatalogMenuEditor.vue";
import type { CatalogMenu } from "~/models/catalogMenu";
import type { ProductCategory } from "~/models/productCategory";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Update Catalog Menu"],
});

const route = useRoute();
const toast = useToast();
const productCategoryStore = useProductCategoryStore();
const catalogMenuStore = useCatalogMenuStore();

const categoryId = parseInt(route.params.id as string);

const loading = ref(true);
const creating = ref(false);
const category = ref<ProductCategory | null>(null);
const menu = ref<CatalogMenu | null>(null);

const loadData = async () => {
  loading.value = true;

  try {
    // Load category
    const { data: categoryData } = await productCategoryStore.fetchProductCategoryById(categoryId);
    category.value = categoryData.value;

    // Load menu
    const { data: menuData, execute } = await catalogMenuStore.fetchMenuByCategoryId(categoryId);
    await execute();
    menu.value = menuData.value;
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося завантажити дані",
      color: "error",
    });
  } finally {
    loading.value = false;
  }
};

const createMenu = async () => {
  creating.value = true;

  try {
    const { data, error } = await catalogMenuStore.onCreateOrUpdateMenu(categoryId, { is_enabled: true });

    if (error.value) {
      toast.add({
        title: "Помилка",
        description: "Не вдалося створити меню",
        color: "error",
      });
      return;
    }

    menu.value = data.value;
    toast.add({
      title: "Успішно",
      description: "Меню створено",
      color: "success",
    });
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося створити меню",
      color: "error",
    });
  } finally {
    creating.value = false;
  }
};

const refreshMenu = async () => {
  const { data: menuData, execute } = await catalogMenuStore.fetchMenuByCategoryId(categoryId);
  await execute();
  menu.value = menuData.value;
};

onMounted(() => {
  loadData();
});
</script>
