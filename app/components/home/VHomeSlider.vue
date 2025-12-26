<template>
  <div v-if="slides.length > 0" class="w-full">
    <UCarousel
      v-slot="{ item }"
      loop
      arrows
      dots
      :autoplay="{ delay: 9000 }"
      :items="slides"
      class="w-full max-w-[100%]"
      :ui="{
        container: 'transition-[height]',
        controls: 'absolute -bottom-8 inset-x-12',
        dots: '-top-7',
        dot: 'w-6 h-1',
      }"
      :prev="{ variant: 'solid' }"
      :next="{ variant: 'solid' }"
    >
      <a :href="item.link || undefined" class="block w-full">
        <VSecureImage
          :file-id="item.file_id"
          alt="Slide"
          width="100%"
          height="400px"
          object-fit="cover"
          img-class="rounded-lg w-full"
        />
      </a>
    </UCarousel>
  </div>
  <div
    v-else
    class="w-full h-[400px] rounded-lg bg-gray-100 flex items-center justify-center"
  >
    <span class="text-gray-400">No slides configured</span>
  </div>
</template>

<script setup lang="ts">
import type { Slide } from "~/models/storeSettings";
import VSecureImage from "~/components/common/VSecureImage.vue";

interface Props {
  slides: Slide[];
}

defineProps<Props>();
</script>
