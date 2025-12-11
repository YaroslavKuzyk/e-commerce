<template>
  <NuxtLink
    to="/product/620682"
    class="px-[16px] py-[10px] v-product-thumb__wrapper"
  >
    <div class="v-product-thumb flex flex-col gap-2">
      <!-- Header -->
      <div class="flex items-center justify-between gap-2">
        <UBadge>Хіт продаж</UBadge>
        <span>Код: <strong>620682</strong></span>
      </div>

      <!-- Image -->
      <div class="py-6 flex items-center justify-center relative">
        <img
          src="https://content1.rozetka.com.ua/goods/images/big/485317909.jpg"
          alt=""
          class="max-w-[172px]"
        />

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

      <!-- Color Variations -->
      <div class="flex items-center gap-1" @click.stop.prevent>
        <NuxtLink
          v-for="item in ['#FECFDA', '#FFFFFF', '#83FFA8']"
          :key="item"
          :to="`/product/${item}`"
          class="w-[16px] h-[16px] rounded-full border border-gray-300"
          :style="`background-color: ${item}`"
        ></NuxtLink>
      </div>

      <!-- Title -->
      <div>
        <h3 class="font-semibold line-clamp-2">
          Моноблок Apple iMac 24" М4 4.5К 10‑ядер GPU
        </h3>
      </div>

      <!-- Reviews -->
      <div class="flex items-center gap-2" @click.stop.prevent>
        <NuxtLink to="/product/620682/reviews">
          <VRating :model-value="Math.random() * 5" readonly />
        </NuxtLink>
        <NuxtLink to="/product/620682/reviews" class="flex items-center gap-1">
          <MessageSquareText class="w-[12px] h-[12px] text-dimmed" />
          <span class="text-dimmed text-xs">150</span>
        </NuxtLink>
      </div>

      <!-- Footer Price -->
      <div class="flex items-end justify-between gap-2">
        <div>
          <div class="text-sm text-dimmed line-through">14 999 грн</div>
          <div class="text-xl font-semibold text-red-500">12 999 грн</div>
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
        <div class="text-sm">
          Екран 23.5" (4480x2520) 4.5K / Apple M4 / RAM 16 ГБ / SSD 256 ГБ /
          Apple M4 Graphics (10 ядер) / без ОД / Wi-Fi / Bluetooth / веб-камера
          / macOS Sequoia / 4.44 кг / рожевий / клавіатура + миша
        </div>
      </div>
    </div>
  </NuxtLink>
</template>

<script lang="ts" setup>
import { ref, onMounted } from "vue";
import { Heart, MessageSquareText, ShoppingCart } from "lucide-vue-next";
import VRating from "@/components/common/VRating.vue";

const hiddenRef = ref<HTMLElement | null>(null);

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
