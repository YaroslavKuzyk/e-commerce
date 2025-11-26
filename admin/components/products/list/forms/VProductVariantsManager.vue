<template>
  <div class="space-y-6 pb-8">
    <div class="flex items-center justify-between pb-2 border-b border-gray-200 dark:border-gray-700">
      <h3 class="font-medium text-lg">Варіації продукту</h3>
      <UButton
        icon="i-heroicons-plus"
        @click="openAddVariantModal"
      >
        Додати варіацію
      </UButton>
    </div>

    <div v-if="product.attributes.length === 0" class="bg-warning-50 dark:bg-warning-900/20 p-4 rounded-lg">
      <p class="text-warning-600 dark:text-warning-400 text-sm">
        Спочатку додайте атрибути до продукту на вкладці "Основна інформація"
      </p>
    </div>

    <div v-else-if="product.variants.length === 0" class="text-center py-8 text-gray-500">
      <p>Немає варіацій. Додайте першу варіацію продукту.</p>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="variant in paginatedVariants"
        :key="variant.id"
        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow p-5 space-y-4"
      >
        <div class="flex items-start justify-between">
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-semibold text-gray-900 dark:text-white">{{ variant.name || variant.sku }}</span>
              <UBadge v-if="variant.is_default" color="primary" size="xs">
                <template #leading>
                  <UIcon name="i-heroicons-star-solid" class="w-3 h-3" />
                </template>
                За замовчуванням
              </UBadge>
              <UBadge
                :color="variant.status === 'published' ? 'success' : 'neutral'"
                variant="subtle"
                size="xs"
              >
                <template #leading>
                  <UIcon :name="variant.status === 'published' ? 'i-heroicons-check-circle' : 'i-heroicons-pencil-square'" class="w-3 h-3" />
                </template>
                {{ variant.status === 'published' ? 'Опубліковано' : 'Чернетка' }}
              </UBadge>
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-3">
              <span class="flex items-center gap-1">
                <UIcon name="i-heroicons-qr-code" class="w-4 h-4" />
                {{ variant.sku }}
              </span>
              <span class="flex items-center gap-1">
                <UIcon name="i-heroicons-link" class="w-4 h-4" />
                {{ variant.slug }}
              </span>
            </div>
          </div>
          <div class="flex items-center gap-1">
            <UButton
              size="sm"
              variant="ghost"
              icon="i-heroicons-pencil"
              @click="openEditVariantModal(variant)"
            />
            <UButton
              size="sm"
              variant="ghost"
              color="error"
              icon="i-heroicons-trash"
              @click="openDeleteVariantModal(variant)"
            />
          </div>
        </div>

        <div class="flex items-center gap-6 text-sm">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center">
              <UIcon name="i-heroicons-currency-dollar" class="w-4 h-4 text-primary-500" />
            </div>
            <div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Ціна</div>
              <div class="font-semibold text-gray-900 dark:text-white">{{ formatPrice(variant.price) }}</div>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="getStockClass(variant.stock)">
              <UIcon name="i-heroicons-cube" class="w-4 h-4" :class="getStockIconClass(variant.stock)" />
            </div>
            <div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Залишок</div>
              <div class="font-semibold" :class="getStockTextClass(variant.stock)">{{ variant.stock }} шт</div>
            </div>
          </div>
        </div>

        <!-- Attribute Values -->
        <div v-if="variant.attribute_values.length > 0" class="flex flex-wrap gap-2">
          <template v-for="attrValue in variant.attribute_values" :key="attrValue.id">
            <div
              v-if="attrValue.color_code"
              class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md border"
              :style="{
                backgroundColor: attrValue.color_code + '20',
                borderColor: attrValue.color_code + '50'
              }"
            >
              <div
                class="w-4 h-4 rounded-full border-2 border-white shadow-sm"
                :style="{ backgroundColor: attrValue.color_code }"
              />
              <span class="text-sm font-medium" :style="{ color: adjustColorBrightness(attrValue.color_code, -40) }">{{ attrValue.value }}</span>
            </div>
            <UBadge
              v-else
              variant="soft"
              size="sm"
            >
              {{ getAttributeName(attrValue.attribute_id) }}: {{ attrValue.value }}
            </UBadge>
          </template>
        </div>

        <!-- Images -->
        <div v-if="variant.images.length > 0" class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
          <div class="flex gap-4 items-center">
            <!-- Primary Image -->
            <div v-if="getPrimaryImage(variant.images)" class="shrink-0">
              <VSecureImage
                :file-id="getPrimaryImage(variant.images)!.file_id"
                :alt="variant.sku"
                width="w-24"
                height="h-24"
                object-fit="cover"
                class="rounded-lg ring-2 ring-primary-500"
              />
            </div>
            <!-- Other Images -->
            <div v-if="getOtherImages(variant.images).length > 0" class="flex gap-3 overflow-x-auto flex-1">
              <div
                v-for="image in getOtherImages(variant.images)"
                :key="image.id"
                class="shrink-0"
              >
                <VSecureImage
                  :file-id="image.file_id"
                  :alt="variant.sku"
                  width="w-16"
                  height="h-16"
                  object-fit="cover"
                  class="rounded-lg border border-gray-200 dark:border-gray-700"
                />
              </div>
            </div>
          </div>
        </div>
        <div v-else class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4 text-center">
          <UIcon name="i-heroicons-photo" class="w-8 h-8 text-gray-300 dark:text-gray-600 mx-auto" />
          <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Немає зображень</p>
        </div>
      </div>
    </div>

    <!-- Add/Edit Variant Modal -->
    <UModal v-model:open="isVariantModalOpen" :title="editingVariant ? 'Редагувати варіацію' : 'Додати варіацію'">
      <template #body>
        <VProductVariantForm
          :product="product"
          :variant="editingVariant"
          @close="closeVariantModal"
          @success="handleVariantSuccess"
        />
      </template>
    </UModal>

    <!-- Delete Variant Modal -->
    <UModal v-model:open="isDeleteModalOpen" title="Видалити варіацію">
      <template #body>
        <div class="space-y-4 p-4">
          <div v-if="deletingVariant" class="p-4 bg-error-50 dark:bg-error-900/20 rounded-lg">
            <p class="text-sm text-error-600 dark:text-error-400">
              Ви збираєтеся видалити варіацію:
            </p>
            <p class="font-semibold mt-2">{{ deletingVariant.name || deletingVariant.sku }}</p>
          </div>

          <p class="text-sm text-gray-600 dark:text-gray-400">
            Ця дія незворотна.
          </p>

          <div class="flex justify-end gap-2">
            <UButton
              variant="outline"
              color="neutral"
              @click="isDeleteModalOpen = false"
            >
              Скасувати
            </UButton>
            <UButton
              color="error"
              :loading="deleteLoading"
              @click="handleDeleteVariant"
            >
              Видалити
            </UButton>
          </div>
        </div>
      </template>
    </UModal>
  </div>
</template>

<script setup lang="ts">
import type { Product, ProductVariant } from "~/models/product";
import VProductVariantForm from "./VProductVariantForm.vue";

interface Props {
  product: Product;
}

interface Emits {
  (e: "refresh"): void;
}

const props = defineProps<Props>();
const emits = defineEmits<Emits>();
const toast = useToast();
const productStore = useProductStore();

const isVariantModalOpen = ref(false);
const editingVariant = ref<ProductVariant | null>(null);

const isDeleteModalOpen = ref(false);
const deletingVariant = ref<ProductVariant | null>(null);
const deleteLoading = ref(false);

// Pagination
const currentPage = ref(1);
const itemsPerPage = ref(5);

const totalPages = computed(() => Math.ceil(props.product.variants.length / itemsPerPage.value));
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage.value);
const endIndex = computed(() => startIndex.value + itemsPerPage.value);

const paginatedVariants = computed(() => {
  return props.product.variants.slice(startIndex.value, endIndex.value);
});

// Pagination meta for parent component
const paginationMeta = computed(() => ({
  current_page: currentPage.value,
  per_page: itemsPerPage.value,
  total: props.product.variants.length,
}));

const updatePage = (page: number) => {
  currentPage.value = page;
};

const updatePerPage = (perPage: number) => {
  itemsPerPage.value = perPage;
  currentPage.value = 1;
};

// Reset to page 1 when variants change
watch(() => props.product.variants.length, () => {
  if (currentPage.value > totalPages.value && totalPages.value > 0) {
    currentPage.value = totalPages.value;
  }
});

// Expose pagination data and methods
defineExpose({
  paginationMeta,
  updatePage,
  updatePerPage,
});

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('uk-UA', {
    style: 'currency',
    currency: 'UAH',
  }).format(Number(price));
};

const getAttributeName = (attributeId: number) => {
  const attr = props.product.attributes.find(a => a.id === attributeId);
  return attr?.name || 'Атрибут';
};

const adjustColorBrightness = (hex: string, percent: number) => {
  const num = parseInt(hex.replace('#', ''), 16);
  const amt = Math.round(2.55 * percent);
  const R = Math.max(0, Math.min(255, (num >> 16) + amt));
  const G = Math.max(0, Math.min(255, ((num >> 8) & 0x00FF) + amt));
  const B = Math.max(0, Math.min(255, (num & 0x0000FF) + amt));
  return `#${(0x1000000 + R * 0x10000 + G * 0x100 + B).toString(16).slice(1)}`;
};

const getStockClass = (stock: number) => {
  if (stock === 0) return 'bg-error-50 dark:bg-error-900/20';
  if (stock <= 5) return 'bg-warning-50 dark:bg-warning-900/20';
  return 'bg-success-50 dark:bg-success-900/20';
};

const getStockIconClass = (stock: number) => {
  if (stock === 0) return 'text-error-500';
  if (stock <= 5) return 'text-warning-500';
  return 'text-success-500';
};

const getStockTextClass = (stock: number) => {
  if (stock === 0) return 'text-error-600 dark:text-error-400';
  if (stock <= 5) return 'text-warning-600 dark:text-warning-400';
  return 'text-gray-900 dark:text-white';
};

const getPrimaryImage = (images: ProductVariant['images']) => {
  return images.find(img => img.is_primary) || images[0];
};

const getOtherImages = (images: ProductVariant['images']) => {
  const primary = getPrimaryImage(images);
  return images
    .filter(img => img.id !== primary?.id)
    .sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
};

const openAddVariantModal = () => {
  editingVariant.value = null;
  isVariantModalOpen.value = true;
};

const openEditVariantModal = (variant: ProductVariant) => {
  editingVariant.value = variant;
  isVariantModalOpen.value = true;
};

const closeVariantModal = () => {
  isVariantModalOpen.value = false;
  editingVariant.value = null;
};

const handleVariantSuccess = () => {
  closeVariantModal();
  emits("refresh");
};

const openDeleteVariantModal = (variant: ProductVariant) => {
  deletingVariant.value = variant;
  isDeleteModalOpen.value = true;
};

const handleDeleteVariant = async () => {
  if (!deletingVariant.value) return;

  deleteLoading.value = true;
  try {
    const { error } = await productStore.onDeleteVariant(props.product.id, deletingVariant.value.id);

    if (error.value) {
      toast.add({
        title: "Помилка",
        description: "Не вдалося видалити варіацію",
        color: "error",
      });
      return;
    }

    toast.add({
      title: "Успішно",
      description: "Варіацію видалено",
      color: "success",
    });

    isDeleteModalOpen.value = false;
    deletingVariant.value = null;
    emits("refresh");
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося видалити варіацію",
      color: "error",
    });
  } finally {
    deleteLoading.value = false;
  }
};
</script>
