<template>
  <div class="bg-white dark:bg-gray-900 rounded-xl p-5 mb-4 shadow-sm border border-gray-100 dark:border-gray-800">
    <!-- Header -->
    <div class="flex items-start justify-between mb-4">
      <div class="flex items-center gap-3">
        <!-- Avatar -->
        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-semibold text-lg">
          {{ getInitials(review.author_name) }}
        </div>
        <div>
          <span class="font-semibold text-gray-900 dark:text-white">{{ review.author_name }}</span>
          <div class="flex items-center gap-2 mt-0.5">
            <div class="flex gap-0.5">
              <Star
                v-for="i in 5"
                :key="i"
                class="w-4 h-4"
                :class="i <= review.rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-200 dark:text-gray-600'"
              />
            </div>
            <span class="text-xs text-gray-400">{{ formatDate(review.created_at) }}</span>
          </div>
        </div>
      </div>
      <!-- Verified badge (if purchased) -->
      <div v-if="review.is_verified" class="flex items-center gap-1 text-xs text-green-600 bg-green-50 dark:bg-green-900/20 px-2 py-1 rounded-full">
        <BadgeCheck class="w-3.5 h-3.5" />
        <span>Підтверджена покупка</span>
      </div>
    </div>

    <!-- Content -->
    <div class="space-y-3">
      <!-- Advantages -->
      <div v-if="review.advantages" class="flex gap-2">
        <div class="shrink-0 mt-0.5">
          <div class="w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
            <ThumbsUp class="w-3 h-3 text-green-600" />
          </div>
        </div>
        <div>
          <span class="text-xs font-medium text-green-600 uppercase tracking-wide">Переваги</span>
          <p class="text-sm text-gray-700 dark:text-gray-300 mt-0.5">{{ review.advantages }}</p>
        </div>
      </div>

      <!-- Disadvantages -->
      <div v-if="review.disadvantages" class="flex gap-2">
        <div class="shrink-0 mt-0.5">
          <div class="w-5 h-5 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
            <ThumbsDown class="w-3 h-3 text-red-600" />
          </div>
        </div>
        <div>
          <span class="text-xs font-medium text-red-600 uppercase tracking-wide">Недоліки</span>
          <p class="text-sm text-gray-700 dark:text-gray-300 mt-0.5">{{ review.disadvantages }}</p>
        </div>
      </div>

      <!-- Comment -->
      <div v-if="review.comment" class="pt-2">
        <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line leading-relaxed">{{ review.comment }}</p>
      </div>
    </div>

    <!-- Media Section -->
    <div v-if="hasMedia" class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
      <!-- Images -->
      <div v-if="review.images && review.images.length" class="flex gap-2 flex-wrap">
        <button
          v-for="image in review.images"
          :key="image.id"
          class="w-20 h-20 rounded-lg overflow-hidden border-2 border-gray-100 dark:border-gray-700 hover:border-primary transition-colors group"
          @click="openImage(image.file_id)"
        >
          <VSecureImage
            :file-id="image.file_id"
            img-class="w-full h-full object-cover group-hover:scale-105 transition-transform"
          />
        </button>
      </div>

      <!-- YouTube Videos -->
      <div v-if="review.youtube_urls && review.youtube_urls.length" class="flex gap-2 flex-wrap" :class="{ 'mt-3': review.images?.length }">
        <a
          v-for="(url, index) in review.youtube_urls"
          :key="index"
          :href="url"
          target="_blank"
          rel="noopener noreferrer"
          class="flex items-center gap-2 px-3 py-2 bg-red-50 dark:bg-red-900/20 text-red-600 rounded-lg text-sm hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors"
        >
          <Youtube class="w-4 h-4" />
          <span>Відео {{ index + 1 }}</span>
          <ExternalLink class="w-3 h-3" />
        </a>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { Star, Youtube, ThumbsUp, ThumbsDown, BadgeCheck, ExternalLink } from "lucide-vue-next";
import VSecureImage from "~/components/common/VSecureImage.vue";

export interface ReviewImage {
  id: number;
  file_id: number;
}

export interface Review {
  id: number;
  author_name: string;
  rating: number;
  advantages?: string;
  disadvantages?: string;
  comment?: string;
  youtube_urls?: string[];
  images?: ReviewImage[];
  created_at: string;
  is_verified?: boolean;
}

interface Props {
  review: Review;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  openImage: [fileId: number];
}>();

const hasMedia = computed(() => {
  return (props.review.images && props.review.images.length > 0) ||
         (props.review.youtube_urls && props.review.youtube_urls.length > 0);
});

const getInitials = (name: string): string => {
  const parts = name.trim().split(/\s+/);
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return name.substring(0, 2).toUpperCase();
};

const formatDate = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleDateString("uk-UA", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
};

const openImage = (fileId: number) => {
  emit("openImage", fileId);
};
</script>
