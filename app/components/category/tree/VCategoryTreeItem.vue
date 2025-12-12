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
      ></div>

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
import VCategoryTreeItemTitle from "./VCategoryTreeItemTitle.vue";

interface IProps {
  onPage?: boolean;
  inModal?: boolean;
}

const { onPage, inModal } = defineProps<IProps>();

const productCategoryStore = useProductCategoryStore();

const isInteractive = computed(() => onPage || inModal);

const categoriesListRef = ref<HTMLElement | null>(null);
const listHeight = ref(0);
const activeCategory = ref<number | null>(null);
const showContent = ref(false);

const handleCategoryHover = (index: number) => {
  if (isInteractive.value) {
    activeCategory.value = index;
    showContent.value = true;
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

const {
  data: categoriesData,
  refresh: refreshCategoriesData,
  status,
} = await productCategoryStore.fetchProductCategories();
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
