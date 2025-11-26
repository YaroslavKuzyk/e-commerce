<template>
  <VSidebarContent title="Редагувати продукт">
    <template #toolbar>
      <div class="flex items-center gap-2 w-full justify-between">
        <UButton
          to="/products/list"
          variant="ghost"
          color="neutral"
        >
          <template #leading>
            <ArrowLeft class="w-5 h-5" />
          </template>
          Повернутись до продуктів
        </UButton>
        <UTabs
          v-if="product"
          v-model="activeTab"
          :items="tabItems"
          class="gap-0"
        />
      </div>
    </template>

    <div v-if="pending" class="flex items-center justify-center py-12">
      <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin" />
    </div>

    <div v-else-if="error" class="text-center py-12">
      <p class="text-error-500">Не вдалося завантажити продукт</p>
      <UButton
        class="mt-4"
        variant="outline"
        @click="refresh"
      >
        Спробувати знову
      </UButton>
    </div>

    <template v-else-if="product">
      <VProductForm
        v-if="activeTab === 'info'"
        :product="product"
        @cancel="router.push('/products/list')"
        @success="handleSuccess"
      />

      <VProductSpecificationsManager
        v-else-if="activeTab === 'specifications'"
        :product="product"
        @refresh="refresh"
      />

      <VProductVariantsManager
        v-else-if="activeTab === 'variants'"
        ref="variantsManagerRef"
        :product="product"
        @refresh="refresh"
      />
    </template>

    <template v-if="activeTab === 'variants' && variantsManagerRef?.paginationMeta" #pagination>
      <VPagination
        :meta="variantsManagerRef.paginationMeta"
        @update:page="(page) => variantsManagerRef?.updatePage(page)"
        @update:per-page="(perPage) => variantsManagerRef?.updatePerPage(perPage)"
      />
    </template>
  </VSidebarContent>
</template>

<script setup lang="ts">
import { ArrowLeft } from "lucide-vue-next";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VProductForm from "~/components/products/list/forms/VProductForm.vue";
import VProductSpecificationsManager from "~/components/products/list/forms/VProductSpecificationsManager.vue";
import VProductVariantsManager from "~/components/products/list/forms/VProductVariantsManager.vue";
import VPagination from "~/components/common/VPagination.vue";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Update Product"],
});

const router = useRouter();
const route = useRoute();
const productStore = useProductStore();

const productId = computed(() => Number(route.params.id));
const variantsManagerRef = ref<InstanceType<typeof VProductVariantsManager> | null>(null);

// Initialize activeTab from URL query or default to 'info'
const initialTab = (route.query.tab as string) || 'info';
const validTabs = ['info', 'specifications', 'variants'];
const activeTab = ref(validTabs.includes(initialTab) ? initialTab : 'info');

// Sync activeTab with URL query
watch(activeTab, (newTab) => {
  router.replace({
    query: { ...route.query, tab: newTab },
  });
});

const { data: productData, pending, error, refresh } = await productStore.fetchProductById(productId.value);

const product = computed(() => productData.value);

const tabItems = computed(() => [
  {
    value: 'info',
    label: 'Основна інформація',
    icon: 'i-heroicons-information-circle',
  },
  {
    value: 'specifications',
    label: `Характеристики (${product.value?.specifications?.length || 0})`,
    icon: 'i-heroicons-list-bullet',
  },
  {
    value: 'variants',
    label: `Варіації (${product.value?.variants?.length || 0})`,
    icon: 'i-heroicons-squares-2x2',
  },
]);

const handleSuccess = async () => {
  await refresh();
};
</script>
