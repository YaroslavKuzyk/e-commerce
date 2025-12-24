<template>
  <NuxtLink
    :to="productUrl"
    class="px-[16px] py-[10px] v-product-thumb__wrapper"
  >
    <div class="v-product-thumb flex flex-col gap-2">
      <!-- Header -->
      <div class="flex items-center justify-between gap-2">
        <UBadge v-if="hasDiscount" color="error"
          >-{{ discountPercent }}%</UBadge
        >
        <span v-else></span>
        <span class="text-sm text-dimmed"
          >Код: <strong>{{ (product as any).sku || product.id }}</strong></span
        >
      </div>

      <!-- Image -->
      <div class="py-6 flex items-center justify-center relative">
        <VSecureImage
          v-if="product.main_image_file_id"
          :fileId="product.main_image_file_id"
          imgClass="max-w-[172px] max-h-[172px] object-contain"
        />
        <div
          v-else
          class="w-[172px] h-[172px] bg-gray-100 rounded flex items-center justify-center"
        >
          <Package class="w-12 h-12 text-gray-400" />
        </div>

        <VFavoriteButton
          :variant-id="variantId"
          class="absolute top-2 right-2 z-1"
        />
        <VComparisonButton
          v-if="product.category_id"
          :variant-id="variantId"
          :category-id="product.category_id"
          class="absolute top-10 right-2 z-1"
        />
      </div>

      <!-- Color Options -->
      <div v-if="colorOptions.length > 0" class="flex gap-1.5">
        <button
          v-for="color in colorOptions"
          :key="color.id"
          class="w-5 h-5 rounded-full border-2 border-gray-200 transition-all hover:scale-110"
          :style="{ backgroundColor: color.color_code || '#ccc' }"
          :title="color.value"
          @click.stop.prevent="selectColor(color.id)"
        />
      </div>

      <!-- Title -->
      <div>
        <h3 class="font-semibold line-clamp-2">
          {{ product.name }}
        </h3>
      </div>

      <!-- Rating (stub) -->
      <div class="flex items-center gap-2">
        <VRating :model-value="rating" readonly />
        <span class="text-sm text-dimmed flex items-center gap-1">
          <MessageSquare class="w-3.5 h-3.5" />
          {{ reviewCount }}
        </span>
      </div>

      <!-- Footer Price -->
      <div class="flex items-end justify-between gap-2">
        <div>
          <div
            v-if="hasDiscount && oldPrice"
            class="text-sm text-dimmed line-through"
          >
            {{ formatPrice(oldPrice) }} грн
          </div>
          <div class="text-xl font-semibold text-primary">
            {{ formatPrice(currentPrice) }} грн
          </div>
        </div>
        <div>
          <UButton
            variant="soft"
            :color="cartStore.isInCart(variantId) ? 'primary' : 'neutral'"
            class="w-[44px] h-[36px] flex items-center justify-center mb-[2px]"
            :loading="isAddingToCart"
            @click.stop.prevent="addToCart"
          >
            <ShoppingCart class="w-[18px] h-[18px]" />
          </UButton>
        </div>
      </div>

      <!-- Hidden Content -->
      <div ref="hiddenRef" class="v-product-thumb__hidden">
        <div class="text-sm text-dimmed">
          {{ product.short_description || "Опис відсутній" }}
        </div>
      </div>
    </div>
  </NuxtLink>
</template>

<script lang="ts" setup>
import { ref, onMounted, computed } from "vue";
import { ShoppingCart, Package, MessageSquare } from "lucide-vue-next";
import type { Product, AttributeValue } from "~/models/product";
import VSecureImage from "~/components/common/VSecureImage.vue";
import VRating from "~/components/common/VRating.vue";
import VFavoriteButton from "~/components/common/VFavoriteButton.vue";
import VComparisonButton from "~/components/common/VComparisonButton.vue";
import { buildProductUrl } from "~/utils/urlBuilder";
import { useCartStore } from "~/stores/useCartStore";

interface Props {
  product: Product;
}

const props = defineProps<Props>();
const router = useRouter();
const cartStore = useCartStore();

// Cart functionality
const isAddingToCart = ref(false);

// Get the variant ID - use variant_id if available (from variants API), otherwise product.id
const variantId = computed(() => {
  const product = props.product as any;
  return product.variant_id || product.id;
});

const addToCart = async () => {
  if (isAddingToCart.value) return;
  isAddingToCart.value = true;
  try {
    await cartStore.add(variantId.value);
  } finally {
    isAddingToCart.value = false;
  }
};

// Build category path from product category
const categoryPath = computed(() => {
  if (!props.product.category) return [];

  const path: string[] = [];
  let currentCategory: typeof props.product.category | undefined =
    props.product.category;

  while (currentCategory) {
    path.unshift(currentCategory.slug);
    currentCategory = currentCategory.parent;
  }

  return path;
});

// Build product URL with category slug and optional variant slug
const productUrl = computed(() => {
  // Check if product has variant_slug (from variants API)
  const variantSlug = (props.product as any).variant_slug;
  return buildProductUrl(categoryPath.value, props.product.slug, variantSlug);
});

const hiddenRef = ref<HTMLElement | null>(null);
const selectedColorId = ref<number | null>(null);

const isNew = computed(() => {
  const createdAt = new Date(props.product.created_at);
  const now = new Date();
  const diffDays = Math.floor(
    (now.getTime() - createdAt.getTime()) / (1000 * 60 * 60 * 24)
  );
  return diffDays <= 30;
});

// Extract color options from product variants
const colorOptions = computed(() => {
  const colors: AttributeValue[] = [];
  const seenIds = new Set<number>();

  if (props.product.variants) {
    for (const variant of props.product.variants) {
      if (variant.attribute_values) {
        for (const attrValue of variant.attribute_values) {
          // Check if this is a color attribute (has color_code)
          if (attrValue.color_code && !seenIds.has(attrValue.id)) {
            colors.push(attrValue);
            seenIds.add(attrValue.id);
          }
        }
      }
    }
  }

  return colors.sort((a, b) => a.sort_order - b.sort_order);
});

// Set default selected color
onMounted(() => {
  if (colorOptions.value.length > 0) {
    // Find default variant's color or use first color
    const defaultVariant = props.product.variants?.find((v) => v.is_default);
    if (defaultVariant?.attribute_values) {
      const defaultColor = defaultVariant.attribute_values.find(
        (av) => av.color_code
      );
      if (defaultColor) {
        selectedColorId.value = defaultColor.id;
        return;
      }
    }
    selectedColorId.value = colorOptions.value[0].id;
  }
});

const selectColor = (colorId: number) => {
  selectedColorId.value = colorId;

  // Find variant with this color
  const variant = props.product.variants?.find((v) => {
    return v.attribute_values?.some((av) => av.id === colorId);
  });

  if (variant?.slug) {
    // Navigate to product page with variant
    const url = buildProductUrl(
      categoryPath.value,
      props.product.slug,
      variant.slug
    );
    router.push(url);
  } else {
    // Navigate to product page without variant
    router.push(productUrl.value);
  }
};

// Rating from product reviews
const rating = computed(() => props.product.average_rating || 0);
const reviewCount = computed(() => props.product.reviews_count || 0);

// Price calculations - support both products and variants
const currentPrice = computed(() => {
  const product = props.product as any;
  // Variants have current_price calculated
  if (product.current_price !== undefined) {
    return product.current_price;
  }
  return product.base_price;
});

const oldPrice = computed(() => {
  const product = props.product as any;
  // If has active discount, show base_price as old price
  if (product.has_active_discount || product.discount_percentage) {
    return product.base_price;
  }
  return null;
});

const hasDiscount = computed(() => {
  const product = props.product as any;
  return (
    product.has_active_discount ||
    (product.discount_percentage && product.discount_percentage > 0) ||
    product.is_clearance
  );
});

const discountPercent = computed(() => {
  const product = props.product as any;
  return product.discount_percentage || 0;
});

const formatPrice = (price: string | number | null) => {
  if (price === null) return "";
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
