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
            activeCategory === category.id ||
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
        <div>
          <div v-if="subcategoriesData.length">
            <h4 class="text-lg font-semibold flex items-center gap-2">
              <VSecureImage
                :fileId="subcategoriesData[0]?.logo_file_id"
                class="w-6 h-6 mr-[2px]"
              />
              Підкатегорії
            </h4>

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
          </div>
        </div>
        <div></div>
        <div></div>
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
import VCategoryTreeItemTitle from "./VCategoryTreeItemTitle.vue";
import VSecureImage from "~/components/common/VSecureImage.vue";

interface IProps {
  onPage?: boolean;
  inModal?: boolean;
}

const { onPage, inModal } = defineProps<IProps>();

const productCategoryStore = useProductCategoryStore();

const {
  data: categoriesData,
  refresh: refreshCategoriesData,
  status,
} = await productCategoryStore.fetchProductCategories();

const isInteractive = computed(() => onPage || inModal);

const categoriesListRef = ref<HTMLElement | null>(null);
const listHeight = ref(0);
const activeCategory = ref<number | null>(null);
const showContent = ref(false);
const subcategoriesData = ref<ProductCategory[]>([]);

const handleCategoryHover = (id: number) => {
  if (isInteractive.value) {
    activeCategory.value = id;
    showContent.value = true;

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
      activeCategory.value = 1;
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
