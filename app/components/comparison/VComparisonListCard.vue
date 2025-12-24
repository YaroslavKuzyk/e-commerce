<template>
  <NuxtLink
    :to="localePath(`/comparison/${list.category.slug}`)"
    class="block p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow"
  >
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <!-- Preview Images -->
        <div class="flex -space-x-2">
          <VSecureImage
            v-for="(imageId, index) in list.preview_images.slice(0, 4)"
            :key="index"
            :fileId="imageId"
            imgClass="w-12 h-12 rounded-full border-2 border-white dark:border-gray-800 object-cover bg-gray-100"
          />
          <div
            v-if="list.products_count > 4"
            class="w-12 h-12 rounded-full border-2 border-white dark:border-gray-800 bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-sm font-medium"
          >
            +{{ list.products_count - 4 }}
          </div>
        </div>

        <!-- Category Info -->
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white">
            {{ list.category.name }}
          </h3>
          <p class="text-sm text-gray-500">
            {{ $t("comparison.productsCount", { count: list.products_count }) }}
          </p>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-2">
        <button
          class="p-2 text-gray-400 hover:text-red-500 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
          @click.stop.prevent="$emit('clear')"
        >
          <Trash2 class="w-5 h-5" />
        </button>
        <ChevronRight class="w-5 h-5 text-gray-400" />
      </div>
    </div>
  </NuxtLink>
</template>

<script lang="ts" setup>
import { ChevronRight, Trash2 } from "lucide-vue-next";
import VSecureImage from "~/components/common/VSecureImage.vue";

interface Props {
  list: {
    category: {
      id: number;
      name: string;
      slug: string;
      logo_file_id?: number | null;
    };
    products_count: number;
    product_ids: number[];
    preview_images: number[];
  };
}

defineProps<Props>();

defineEmits<{
  clear: [];
}>();

const localePath = useLocalePath();
</script>
