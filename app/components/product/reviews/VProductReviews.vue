<template>
  <div class="mt-12">
    <!-- Section Header -->
    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center gap-3">
        <div class="w-1 h-8 bg-primary rounded-full"></div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Відгуки покупців</h2>
        <span v-if="stats" class="text-sm text-gray-500 dark:text-gray-400">
          ({{ stats.total_reviews }})
        </span>
      </div>
      <UButton
        @click="isFormOpen = true"
        size="lg"
        class="shadow-sm"
      >
        <template #leading>
          <PenLine class="w-4 h-4" />
        </template>
        Написати відгук
      </UButton>
    </div>

    <div v-if="isLoading" class="flex justify-center py-12">
      <div class="flex flex-col items-center gap-3">
        <UButton loading variant="ghost" size="xl" />
        <span class="text-sm text-gray-500">Завантаження відгуків...</span>
      </div>
    </div>

    <div v-else>
      <!-- Stats Section -->
      <VReviewStats
        v-if="stats && stats.total_reviews > 0"
        :total-reviews="stats.total_reviews"
        :average-rating="stats.average_rating"
        :rating-distribution="stats.rating_distribution"
        class="mb-8"
      />

      <!-- Reviews List -->
      <div v-if="reviews.length > 0" class="space-y-4">
        <TransitionGroup name="review-list">
          <VReviewItem
            v-for="review in reviews"
            :key="review.id"
            :review="review"
            @open-image="openImage"
          />
        </TransitionGroup>

        <!-- Pagination -->
        <div v-if="meta && meta.last_page > 1" class="flex justify-center mt-8 pt-4">
          <UPagination
            v-model="currentPage"
            :page-count="meta.per_page"
            :total="meta.total"
            :ui="{
              wrapper: 'gap-1',
              base: 'min-w-[40px] h-10 rounded-lg',
            }"
          />
        </div>
      </div>

      <!-- No Reviews State -->
      <div v-else class="text-center py-16">
        <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
          <MessageSquare class="w-10 h-10 text-gray-400" />
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
          Поки немає відгуків
        </h3>
        <p class="text-gray-500 dark:text-gray-400 mb-6">
          Будьте першим, хто залишить відгук про цей товар!
        </p>
        <UButton @click="isFormOpen = true" variant="soft">
          <template #leading>
            <PenLine class="w-4 h-4" />
          </template>
          Написати перший відгук
        </UButton>
      </div>
    </div>

    <!-- Review Form Modal -->
    <VReviewForm
      v-model:open="isFormOpen"
      :product-slug="productSlug"
      @submitted="onReviewSubmitted"
    />

    <!-- Image Lightbox -->
    <UModal v-model:open="isLightboxOpen" :ui="{ width: 'sm:max-w-4xl' }">
      <template #content>
        <div class="relative p-4">
          <VSecureImage
            v-if="lightboxFileId"
            :file-id="lightboxFileId"
            img-class="max-w-full max-h-[85vh] mx-auto rounded-lg"
          />
          <UButton
            icon="i-lucide-x"
            variant="ghost"
            class="absolute top-2 right-2"
            @click="isLightboxOpen = false"
          />
        </div>
      </template>
    </UModal>
  </div>
</template>

<script lang="ts" setup>
import { PenLine, MessageSquare } from "lucide-vue-next";
import VReviewStats from "./VReviewStats.vue";
import VReviewItem, { type Review } from "./VReviewItem.vue";
import VReviewForm from "./VReviewForm.vue";
import VSecureImage from "~/components/common/VSecureImage.vue";

interface Props {
  productSlug: string;
}

const props = defineProps<Props>();

const client = useSanctumClient();

interface ReviewStats {
  total_reviews: number;
  average_rating: number;
  rating_distribution: Record<number, number>;
}

interface Meta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

const reviews = ref<Review[]>([]);
const stats = ref<ReviewStats | null>(null);
const meta = ref<Meta | null>(null);
const isLoading = ref(true);
const currentPage = ref(1);
const isFormOpen = ref(false);
const isLightboxOpen = ref(false);
const lightboxFileId = ref<number | null>(null);

const fetchReviews = async () => {
  isLoading.value = true;

  try {
    const response = await client<{
      success: boolean;
      data: Review[];
      stats: ReviewStats;
      meta: Meta;
    }>(`/api/products/${props.productSlug}/reviews?page=${currentPage.value}`);

    reviews.value = response.data;
    stats.value = response.stats;
    meta.value = response.meta;
  } catch (e) {
    console.error("Failed to fetch reviews:", e);
  } finally {
    isLoading.value = false;
  }
};

const openImage = (fileId: number) => {
  lightboxFileId.value = fileId;
  isLightboxOpen.value = true;
};

const onReviewSubmitted = () => {
  // Refresh reviews after submission
  currentPage.value = 1;
  fetchReviews();
};

// Watch page changes
watch(currentPage, () => {
  fetchReviews();
});

// Fetch on mount
onMounted(() => {
  fetchReviews();
});

// Watch for product slug changes
watch(
  () => props.productSlug,
  () => {
    currentPage.value = 1;
    fetchReviews();
  }
);
</script>

<style scoped>
.review-list-enter-active,
.review-list-leave-active {
  transition: all 0.3s ease;
}

.review-list-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}

.review-list-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
</style>
