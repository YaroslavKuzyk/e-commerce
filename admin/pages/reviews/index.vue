<template>
  <VSidebarContent :title="$t('reviews.title')">
    <template #toolbar>
      <div class="flex items-center justify-between w-full gap-2">
        <div class="flex items-center gap-2 w-full flex-wrap">
          <UInput
            v-model="filters.search"
            :placeholder="$t('reviews.searchByAuthor')"
            class="w-[200px]"
          >
            <template #leading>
              <Search class="w-5 h-5" />
            </template>
          </UInput>
          <USelectMenu
            v-model="filters.status"
            :items="statusOptions"
            :placeholder="$t('common.status')"
            value-key="value"
            label-key="label"
            class="w-[150px]"
          >
            <template #trailing>
              <UButton
                v-if="filters.status"
                size="sm"
                variant="link"
                :aria-label="$t('common.clear')"
                @click.stop="filters.status = null"
                color="neutral"
              >
                <CircleX class="w-4 h-4" />
              </UButton>
            </template>
          </USelectMenu>
          <USelectMenu
            v-model="filters.rating"
            :items="ratingOptions"
            :placeholder="$t('reviews.rating')"
            value-key="value"
            label-key="label"
            class="w-[120px]"
          >
            <template #trailing>
              <UButton
                v-if="filters.rating"
                size="sm"
                variant="link"
                :aria-label="$t('common.clear')"
                @click.stop="filters.rating = null"
                color="neutral"
              >
                <CircleX class="w-4 h-4" />
              </UButton>
            </template>
          </USelectMenu>
          <UButton
            v-if="hasActiveFilters"
            variant="ghost"
            @click="clearFilters"
          >
            <template #leading>
              <X class="w-5 h-5" />
            </template>
            {{ $t("common.clearFilters") }}
          </UButton>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <!-- Stats badges -->
          <div class="flex items-center gap-2">
            <UBadge variant="subtle" color="warning">
              {{ $t('reviews.pending') }}: {{ stats?.pending || 0 }}
            </UBadge>
            <UBadge variant="subtle" color="success">
              {{ $t('reviews.approved') }}: {{ stats?.approved || 0 }}
            </UBadge>
          </div>
        </div>
      </div>
    </template>

    <HasPermissions :required-permissions="['Read Product Reviews']">
      <!-- View Review Modal -->
      <UModal
        v-model:open="isViewModalOpen"
        :title="$t('reviews.viewReview')"
        :description="selectedReview?.product?.name"
      >
        <template #body>
          <div v-if="selectedReview" class="space-y-4">
            <!-- Product info -->
            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
              <div
                v-if="selectedReview.product?.main_image_file_id"
                class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0"
              >
                <VSecureImage
                  :file-id="selectedReview.product.main_image_file_id"
                  :alt="selectedReview.product?.name || ''"
                  width="w-12"
                  height="h-12"
                  object-fit="cover"
                />
              </div>
              <div
                v-else
                class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0"
              >
                <Package class="w-6 h-6 text-gray-400" />
              </div>
              <div>
                <p class="font-medium">{{ selectedReview.product?.name }}</p>
                <p class="text-sm text-gray-500">{{ selectedReview.product?.slug }}</p>
              </div>
            </div>

            <!-- Author info -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-500">{{ $t('reviews.author') }}</p>
                <p class="font-medium">{{ selectedReview.author_name }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">{{ $t('reviews.email') }}</p>
                <p class="font-medium">{{ selectedReview.author_email || '—' }}</p>
              </div>
            </div>

            <!-- Rating -->
            <div>
              <p class="text-sm text-gray-500 mb-1">{{ $t('reviews.rating') }}</p>
              <div class="flex items-center gap-1">
                <Star
                  v-for="i in 5"
                  :key="i"
                  class="w-5 h-5"
                  :class="i <= selectedReview.rating ? 'text-green-500 fill-green-500' : 'text-gray-300'"
                />
                <span class="ml-2 font-medium">{{ selectedReview.rating }}/5</span>
              </div>
            </div>

            <!-- Advantages -->
            <div v-if="selectedReview.advantages">
              <p class="text-sm text-gray-500 mb-1">{{ $t('reviews.advantages') }}</p>
              <p class="text-green-600 dark:text-green-400">{{ selectedReview.advantages }}</p>
            </div>

            <!-- Disadvantages -->
            <div v-if="selectedReview.disadvantages">
              <p class="text-sm text-gray-500 mb-1">{{ $t('reviews.disadvantages') }}</p>
              <p class="text-red-600 dark:text-red-400">{{ selectedReview.disadvantages }}</p>
            </div>

            <!-- Comment -->
            <div v-if="selectedReview.comment">
              <p class="text-sm text-gray-500 mb-1">{{ $t('reviews.comment') }}</p>
              <p>{{ selectedReview.comment }}</p>
            </div>

            <!-- Images -->
            <div v-if="selectedReview.images && selectedReview.images.length > 0">
              <p class="text-sm text-gray-500 mb-2">{{ $t('reviews.images') }}</p>
              <div class="flex flex-wrap gap-2">
                <div
                  v-for="image in selectedReview.images"
                  :key="image.id"
                  class="w-20 h-20 rounded-lg overflow-hidden bg-gray-100"
                >
                  <VSecureImage
                    :file-id="image.file_id"
                    :alt="'Review image'"
                    width="w-20"
                    height="h-20"
                    object-fit="cover"
                  />
                </div>
              </div>
            </div>

            <!-- YouTube Videos -->
            <div v-if="selectedReview.youtube_urls && selectedReview.youtube_urls.length > 0">
              <p class="text-sm text-gray-500 mb-2">{{ $t('reviews.videos') }}</p>
              <div class="space-y-1">
                <a
                  v-for="(url, index) in selectedReview.youtube_urls"
                  :key="index"
                  :href="url"
                  target="_blank"
                  class="flex items-center gap-2 text-blue-600 hover:text-blue-800"
                >
                  <Youtube class="w-4 h-4" />
                  {{ url }}
                </a>
              </div>
            </div>

            <!-- Created at -->
            <div>
              <p class="text-sm text-gray-500">{{ $t('reviews.createdAt') }}</p>
              <p>{{ formatDate(selectedReview.created_at) }}</p>
            </div>

            <USeparator />

            <!-- Actions -->
            <div class="flex justify-end gap-2">
              <UButton
                type="button"
                variant="outline"
                color="neutral"
                @click="isViewModalOpen = false"
              >
                <template #leading>
                  <Ban class="w-4 h-4" />
                </template>
                {{ $t("common.close") }}
              </UButton>
              <HasPermissions :required-permissions="['Update Product Review']">
                <UButton
                  v-if="selectedReview.status !== 'rejected'"
                  type="button"
                  color="error"
                  @click="handleReject(selectedReview)"
                >
                  <template #leading>
                    <XIcon class="w-4 h-4" />
                  </template>
                  {{ $t('reviews.reject') }}
                </UButton>
                <UButton
                  v-if="selectedReview.status !== 'approved'"
                  type="button"
                  color="success"
                  @click="handleApprove(selectedReview)"
                >
                  <template #leading>
                    <Send class="w-4 h-4" />
                  </template>
                  {{ $t('reviews.approve') }}
                </UButton>
              </HasPermissions>
            </div>
          </div>
        </template>
      </UModal>

      <!-- Delete Modal -->
      <UModal
        v-model:open="isDeleteModalOpen"
        :title="$t('reviews.deleteTitle')"
        :description="$t('reviews.deleteWarning')"
      >
        <template #body>
          <div class="space-y-4">
            <div v-if="reviewToDelete" class="p-4 bg-error-50 dark:bg-error-900/20 rounded-lg">
              <p class="text-sm text-error-600 dark:text-error-400">
                {{ $t("reviews.deleteConfirm") }}
              </p>
              <p class="font-semibold mt-2">{{ reviewToDelete.author_name }}</p>
              <p class="text-sm text-gray-500 mt-1">{{ reviewToDelete.product?.name }}</p>
            </div>

            <USeparator />

            <div class="flex justify-end gap-2">
              <UButton
                type="button"
                variant="outline"
                color="neutral"
                @click="isDeleteModalOpen = false"
              >
                <template #leading>
                  <Ban class="w-4 h-4" />
                </template>
                {{ $t("common.cancel") }}
              </UButton>
              <UButton
                type="button"
                color="error"
                :loading="deleteLoading"
                @click="handleDeleteReview"
              >
                <template #leading>
                  <Send class="w-4 h-4" />
                </template>
                {{ $t("common.confirm") }}
              </UButton>
            </div>
          </div>
        </template>
      </UModal>

      <UTable
        :data="reviewsData || []"
        :columns="columns"
        :loading="pending"
      >
        <template #product-cell="{ row }">
          <div v-if="row.original.product" class="max-w-[200px]">
            <p class="font-medium truncate">{{ row.original.product.name }}</p>
          </div>
          <span v-else class="text-gray-400">—</span>
        </template>

        <template #author-cell="{ row }">
          <div>
            <p class="font-medium">{{ row.original.author_name }}</p>
            <p class="text-sm text-gray-500">{{ row.original.author_email }}</p>
          </div>
        </template>

        <template #rating-cell="{ row }">
          <div class="flex items-center gap-1">
            <Star class="w-4 h-4 text-green-500 fill-green-500" />
            <span class="font-medium">{{ row.original.rating }}</span>
          </div>
        </template>

        <template #content-cell="{ row }">
          <div class="max-w-[300px]">
            <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
              {{ row.original.comment || row.original.advantages || $t('reviews.noContent') }}
            </p>
          </div>
        </template>

        <template #status-cell="{ row }">
          <UBadge
            :color="getStatusColor(row.original.status)"
            variant="subtle"
          >
            {{ $t(`reviews.${row.original.status}`) }}
          </UBadge>
        </template>

        <template #created_at-cell="{ row }">
          <span class="text-gray-600 dark:text-gray-400">
            {{ formatDate(row.original.created_at) }}
          </span>
        </template>

        <template #actions-cell="{ row }">
          <div class="flex items-center gap-1">
            <UButton
              size="sm"
              variant="ghost"
              icon="i-heroicons-eye"
              @click="handleView(row.original)"
            />
            <HasPermissions :required-permissions="['Update Product Review']">
              <UButton
                v-if="row.original.status !== 'approved'"
                size="sm"
                variant="ghost"
                color="success"
                @click="handleApprove(row.original)"
              >
                <Check class="w-4 h-4" />
              </UButton>
              <UButton
                v-if="row.original.status !== 'rejected'"
                size="sm"
                variant="ghost"
                color="warning"
                @click="handleReject(row.original)"
              >
                <XIcon class="w-4 h-4" />
              </UButton>
            </HasPermissions>
            <HasPermissions :required-permissions="['Delete Product Review']">
              <UButton
                size="sm"
                variant="ghost"
                color="error"
                icon="i-heroicons-trash"
                @click="handleDelete(row.original)"
              />
            </HasPermissions>
          </div>
        </template>
      </UTable>
    </HasPermissions>

    <template #pagination>
      <VPagination
        :meta="meta"
        @update:page="(page) => filters.page = page"
        @update:per-page="(perPage) => filters.per_page = perPage"
      />
    </template>
  </VSidebarContent>
</template>

<script setup lang="ts">
import { Search, CircleX, X, Star, Check, X as XIcon, Package, Youtube, Ban, Send } from "lucide-vue-next";
import HasPermissions from "~/components/common/VHasPermissions.vue";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VPagination from "~/components/common/VPagination.vue";
import VSecureImage from "~/components/common/VSecureImage.vue";
import type { ProductReview, ProductReviewFilters, ReviewStatus } from "~/models/productReview";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Read Product Reviews"],
});

const { t } = useI18n();
const productReviewStore = useProductReviewStore();
const toast = useToast();

const isViewModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedReview = ref<ProductReview | null>(null);
const reviewToDelete = ref<ProductReview | null>(null);
const deleteLoading = ref(false);

const filters = ref({
  search: "",
  status: null as ReviewStatus | null,
  rating: null as number | null,
  page: 1,
  per_page: 15,
});

const statusOptions = computed(() => [
  { label: t("reviews.pending"), value: "pending" },
  { label: t("reviews.approved"), value: "approved" },
  { label: t("reviews.rejected"), value: "rejected" },
]);

const ratingOptions = computed(() => [
  { label: "⭐ 1", value: 1 },
  { label: "⭐ 2", value: 2 },
  { label: "⭐ 3", value: 3 },
  { label: "⭐ 4", value: 4 },
  { label: "⭐ 5", value: 5 },
]);

const columns = computed(() => [
  { id: 'product', header: t('reviews.product'), meta: { class: { th: 'w-[200px]' } } },
  { id: 'author', header: t('reviews.author'), meta: { class: { th: 'w-[180px]' } } },
  { id: 'rating', header: t('reviews.rating'), meta: { class: { th: 'w-[80px]' } } },
  { id: 'content', header: t('reviews.comment') },
  { id: 'status', header: t('common.status'), meta: { class: { th: 'w-[120px]' } } },
  { id: 'created_at', header: t('reviews.createdAt'), meta: { class: { th: 'w-[120px]' } } },
  { id: 'actions', header: t('common.actions'), meta: { class: { th: 'w-32 text-end', td: 'text-end' } } },
]);

// Fetch stats
const { data: statsData } = await productReviewStore.fetchProductReviewStats();
const stats = computed(() => statsData.value);

const {
  data: reviewsData,
  meta,
  pending,
  hasActiveFilters,
  clearFilters,
  refresh: internalRefresh,
} = await usePaginationList<typeof filters.value, ProductReview>({
  key: 'product-reviews-list',
  filters,
  fetchFunction: (filters?: ProductReviewFilters) => productReviewStore.fetchProductReviewsPromise(filters),
  debounceFields: ["search"],
});

const getStatusColor = (status: ReviewStatus) => {
  switch (status) {
    case 'approved': return 'success';
    case 'rejected': return 'error';
    default: return 'warning';
  }
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('uk-UA', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const refreshReviews = async () => {
  await internalRefresh();
  const { data } = await productReviewStore.fetchProductReviewStats();
  if (data.value) {
    statsData.value = data.value;
  }
};

const handleView = (review: ProductReview) => {
  selectedReview.value = review;
  isViewModalOpen.value = true;
};

const handleApprove = async (review: ProductReview) => {
  const { error } = await productReviewStore.onApproveReview(review.id);

  if (error.value) {
    toast.add({
      title: t("common.error"),
      description: t("reviews.approveError"),
      color: "error",
    });
    return;
  }

  toast.add({
    title: t("common.success"),
    description: t("reviews.approveSuccess"),
    color: "success",
  });

  isViewModalOpen.value = false;
  await refreshReviews();
};

const handleReject = async (review: ProductReview) => {
  const { error } = await productReviewStore.onRejectReview(review.id);

  if (error.value) {
    toast.add({
      title: t("common.error"),
      description: t("reviews.rejectError"),
      color: "error",
    });
    return;
  }

  toast.add({
    title: t("common.success"),
    description: t("reviews.rejectSuccess"),
    color: "success",
  });

  isViewModalOpen.value = false;
  await refreshReviews();
};

const handleDelete = (review: ProductReview) => {
  reviewToDelete.value = review;
  isDeleteModalOpen.value = true;
};

const handleDeleteReview = async () => {
  if (!reviewToDelete.value) return;

  deleteLoading.value = true;
  try {
    const { error } = await productReviewStore.onDeleteReview(reviewToDelete.value.id);

    if (error.value) {
      toast.add({
        title: t("common.error"),
        description: t("reviews.deleteError"),
        color: "error",
      });
      return;
    }

    toast.add({
      title: t("common.success"),
      description: t("reviews.deleteSuccess"),
      color: "success",
    });

    isDeleteModalOpen.value = false;
    reviewToDelete.value = null;
    await refreshReviews();
  } catch (error) {
    toast.add({
      title: t("common.error"),
      description: t("reviews.deleteError"),
      color: "error",
    });
  } finally {
    deleteLoading.value = false;
  }
};
</script>
