<template>
  <div class="w-full">
    <Transition name="fade">
      <div
        v-if="onPage && showContent"
        class="fixed inset-0 bg-black/50 z-40"
        @mouseenter="handleLeave"
      />
    </Transition>

    <div
      class="flex items-start bg-white w-full"
      :class="{ 'relative z-50 overlay': onPage && showContent }"
    >
      <div ref="categoriesListRef" class="max-w-[340px] w-full shrink-0">
        <VCategoryTreeItemTitle
          v-for="category in categoriesData"
          :key="category.id"
          :category="category"
          :active="
            activeCategoryId === category.id ||
            (!isInteractive && category.id === 2)
          "
          @mouseenter="handleCategoryHover(category.id)"
        />
      </div>

      <div
        v-if="!isInteractive || showContent"
        class="flex-1 overflow-auto pl-12 bg-white"
        :style="{ maxHeight: `${listHeight}px` }"
      >
        <div v-if="activeCategory">
          <router-link
            :to="`/category/${activeCategory.slug}`"
            class="text-lg font-semibold flex items-center gap-2 mb-4"
          >
            <VSecureImage
              v-if="activeCategory.logo_file_id"
              :fileId="activeCategory.logo_file_id"
              class="w-6 h-6 mr-[2px]"
            />
            {{ activeCategory.name }}
          </router-link>

          <!-- Show catalog menu if exists (even if empty) -->
          <VCatalogMenuDisplay
            v-if="activeCatalogMenu"
            :menu="activeCatalogMenu"
          />

          <!-- Show subcategories only if NO catalog menu exists -->
          <template v-else-if="subcategoriesData.length">
            <router-link
              :to="`/category/${activeCategory.slug}/${subcategoriesData[0].slug}`"
              class="text-md font-medium flex items-center gap-2 text-gray-600"
            >
              Підкатегорії
            </router-link>

            <div class="pl-9">
              <router-link
                v-for="subcategory in subcategoriesData"
                :key="subcategory.id"
                :to="`/category/${subcategory.slug}`"
                class="block py-2"
              >
                {{ subcategory.name }}
              </router-link>
            </div>
          </template>
        </div>
      </div>

      <div
        v-if="onPage"
        class="flex-1 w-full"
        :class="{
          'opacity-0 pointer-events-none !w-[0px] !flex-0': showContent,
        }"
      >
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ProductCategory } from "~/models/productCategory";
import type { CatalogMenu } from "~/models/catalogMenu";
import VCategoryTreeItemTitle from "./VCategoryTreeItemTitle.vue";
import VSecureImage from "~/components/common/VSecureImage.vue";
import VCatalogMenuDisplay from "~/components/category/menu/VCatalogMenuDisplay.vue";

interface IProps {
  onPage?: boolean;
  inModal?: boolean;
}

const { onPage, inModal } = defineProps<IProps>();

const productCategoryStore = useProductCategoryStore();
const catalogMenuStore = useCatalogMenuStore();

const {
  data: categoriesData,
  refresh: refreshCategoriesData,
  status,
} = await productCategoryStore.fetchProductCategories();

// Fetch all catalog menus for root categories
await catalogMenuStore.fetchAllMenus();

const isInteractive = computed(() => onPage || inModal);

const categoriesListRef = ref<HTMLElement | null>(null);
const listHeight = ref(0);
const activeCategoryId = ref<number | null>(null);
const showContent = ref(false);
const subcategoriesData = ref<ProductCategory[]>([]);
const activeCatalogMenu = ref<CatalogMenu | null>(null);

const activeCategory = computed(() =>
  categoriesData.value?.find(
    (category) => category.id === activeCategoryId.value
  )
);

const handleCategoryHover = (id: number) => {
  if (isInteractive.value) {
    activeCategoryId.value = id;
    showContent.value = true;

    // Get catalog menu for this category
    activeCatalogMenu.value = catalogMenuStore.getMenuFromCache(id);

    // Fallback to subcategories if no menu
    subcategoriesData.value =
      categoriesData.value?.find((category) => category.id === id)
        ?.subcategories || [];
  }
};

const handleLeave = () => {
  showContent.value = false;
};

onMounted(() => {
  nextTick(() => {
    if (categoriesListRef.value) {
      listHeight.value = categoriesListRef.value.scrollHeight;
    }

    if (inModal) {
      activeCategoryId.value = 1;
      showContent.value = true;
    }
  });
});
</script>

<style scoped lang="scss">
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.overlay {
  &::before {
    content: "";
    position: absolute;
    top: -0.75rem;
    left: -0.75rem;
    right: -0.75rem;
    bottom: -0.75rem;
    z-index: -1;
    width: calc(100% + 1.5rem);
    height: calc(100% + 1.5rem);
    border-radius: 1rem;
    background-color: rgba(255, 255, 255);
  }
}
</style>
