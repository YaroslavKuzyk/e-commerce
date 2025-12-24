<template>
  <div class="bg-gradient-to-br from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700">
    <div class="flex flex-col lg:flex-row gap-8 items-center lg:items-start">
      <!-- Rating Summary -->
      <div class="flex flex-col items-center text-center lg:pr-8 lg:border-r border-gray-200 dark:border-gray-700">
        <div class="text-5xl font-bold text-gray-900 dark:text-white">
          {{ averageRating.toFixed(1) }}
        </div>
        <div class="flex gap-1 my-3">
          <Star
            v-for="i in 5"
            :key="i"
            class="w-6 h-6"
            :class="i <= Math.round(averageRating) ? 'text-yellow-400 fill-yellow-400' : 'text-gray-200 dark:text-gray-600'"
          />
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400">
          {{ totalReviews }} {{ reviewsWord }}
        </div>
      </div>

      <!-- Rating Distribution -->
      <div class="flex-1 w-full lg:w-auto min-w-[280px]">
        <div
          v-for="rating in [5, 4, 3, 2, 1]"
          :key="rating"
          class="flex items-center gap-3 mb-2.5 last:mb-0 group cursor-default"
        >
          <div class="flex items-center gap-1.5 shrink-0">
            <span class="w-4 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ rating }}</span>
            <Star class="w-4 h-4 text-yellow-400 fill-yellow-400" />
          </div>
          <div class="flex-1 h-3 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
            <div
              class="h-full rounded-full transition-all duration-700 ease-out"
              :class="getBarColor(rating)"
              :style="{ width: `${getPercentage(rating)}%` }"
            />
          </div>
          <div class="flex items-center gap-2 shrink-0 min-w-[60px] justify-end">
            <span class="text-sm font-medium text-gray-600 dark:text-gray-400 tabular-nums">
              {{ ratingDistribution[rating] || 0 }}
            </span>
            <span class="text-xs text-gray-400 tabular-nums w-10 text-right">
              ({{ getPercentage(rating).toFixed(0) }}%)
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { Star } from "lucide-vue-next";

interface Props {
  totalReviews: number;
  averageRating: number;
  ratingDistribution: Record<number, number>;
}

const props = defineProps<Props>();

const getPercentage = (rating: number): number => {
  if (props.totalReviews === 0) return 0;
  return ((props.ratingDistribution[rating] || 0) / props.totalReviews) * 100;
};

const getBarColor = (rating: number): string => {
  if (rating >= 4) return 'bg-green-500';
  if (rating === 3) return 'bg-yellow-500';
  return 'bg-red-400';
};

const reviewsWord = computed(() => {
  const count = props.totalReviews;
  const lastDigit = count % 10;
  const lastTwoDigits = count % 100;

  if (lastTwoDigits >= 11 && lastTwoDigits <= 14) return "відгуків";
  if (lastDigit === 1) return "відгук";
  if (lastDigit >= 2 && lastDigit <= 4) return "відгуки";
  return "відгуків";
});
</script>
