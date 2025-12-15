<template>
  <NuxtLink
    :to="`/product/${product.slug}`"
    class="px-[16px] py-[10px] v-product-thumb__wrapper"
  >
    <div class="v-product-thumb flex flex-col gap-2">
      <!-- Header -->
      <div class="flex items-center justify-between gap-2">
        <UBadge v-if="isNew">Новинка</UBadge>
        <span class="text-sm text-dimmed">Код: <strong>{{ product.id }}</strong></span>
      </div>

      <!-- Image -->
      <div class="py-6 flex items-center justify-center relative">
        <VSecureImage
          v-if="product.main_image_file_id"
          :fileId="product.main_image_file_id"
          imgClass="max-w-[172px] max-h-[172px] object-contain"
        />
        <div v-else class="w-[172px] h-[172px] bg-gray-100 rounded flex items-center justify-center">
          <Package class="w-12 h-12 text-gray-400" />
        </div>

        <UButton
          variant="ghost"
          color="error"
          size="icon"
          class="absolute top-2 right-2 p-1 z-1"
          @click.stop.prevent
        >
          <Heart />
        </UButton>
      </div>

      <!-- Title -->
      <div>
        <h3 class="font-semibold line-clamp-2">
          {{ product.name }}
        </h3>
      </div>

      <!-- Category -->
      <div v-if="product.category" class="text-sm text-dimmed">
        {{ product.category.name }}
      </div>

      <!-- Footer Price -->
      <div class="flex items-end justify-between gap-2">
        <div>
          <div class="text-xl font-semibold text-primary">{{ formatPrice(product.base_price) }} грн</div>
        </div>
        <div>
          <UButton
            variant="soft"
            class="w-[44px] h-[36px] flex items-center justify-center mb-[2px]"
            @click.stop.prevent
          >
            <ShoppingCart class="w-[18px] h-[18px]" />
          </UButton>
        </div>
      </div>

      <!-- Hidden Content -->
      <div ref="hiddenRef" class="v-product-thumb__hidden">
        <div class="text-sm text-dimmed">
          {{ product.short_description || 'Опис відсутній' }}
        </div>
      </div>
    </div>
  </NuxtLink>
</template>

<script lang="ts" setup>
import { ref, onMounted, computed } from "vue";
import { Heart, ShoppingCart, Package } from "lucide-vue-next";
import type { Product } from "~/models/product";
import VSecureImage from "~/components/common/VSecureImage.vue";

interface Props {
  product: Product;
}

const props = defineProps<Props>();

const hiddenRef = ref<HTMLElement | null>(null);

const isNew = computed(() => {
  const createdAt = new Date(props.product.created_at);
  const now = new Date();
  const diffDays = Math.floor((now.getTime() - createdAt.getTime()) / (1000 * 60 * 60 * 24));
  return diffDays <= 30;
});

const formatPrice = (price: string) => {
  return Number(price).toLocaleString("uk-UA");
};

onMounted(() => {
  if (hiddenRef.value) {
    const height = hiddenRef.value.offsetHeight;
    const wrapper = hiddenRef.value.closest(
      ".v-product-thumb__wrapper"
    ) as HTMLElement | null;
    wrapper?.style.setProperty("--actual-hidden-height", `${height}px`);
  }
});
</script>

<style lang="scss" scoped>
.v-product-thumb__wrapper {
  --hidden-height: 0px;

  position: relative;
  z-index: 1;

  &::after {
    content: "";
    position: absolute;
    top: -8px;
    left: -8px;
    width: calc(100% + 16px);
    height: calc(100% + 16px + var(--hidden-height));
    background: var(--ui-text-inverted);
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    opacity: 0;
    z-index: -1;
    pointer-events: none;
  }

  &:hover {
    --hidden-height: var(--actual-hidden-height, 80px);

    z-index: 10;

    &::after {
      opacity: 1;
    }

    .v-product-thumb__hidden {
      pointer-events: all;
      opacity: 1;
    }
  }
}

.v-product-thumb__hidden {
  position: absolute;
  top: 100%;
  left: -8px;
  width: calc(100% + 16px);
  padding: 8px 16px 16px;
  pointer-events: none;
  opacity: 0;
}
</style>
