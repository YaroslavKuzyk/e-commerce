<template>
  <UPage>
    <UContainer>
      <!-- Breadcrumbs -->
      <VBreadcrumbs
        v-if="breadcrumbs.length"
        :items="breadcrumbs"
        class="pt-6"
      />

      <div v-if="isLoading" class="flex justify-center py-12">
        <UButton loading variant="ghost" />
      </div>

      <div v-else-if="product" class="py-6">
        <div class="flex gap-8">
          <!-- Product Images -->
          <div class="w-1/2">
            <div class="aspect-square bg-muted rounded-lg overflow-hidden">
              <VSecureImage
                v-if="mainImageFileId"
                :file-id="mainImageFileId"
                :alt="product.name"
                width="100%"
                height="100%"
                object-fit="cover"
                img-class="w-full h-full"
              />
              <div
                v-else
                class="w-full h-full flex items-center justify-center"
              >
                <Package class="w-24 h-24 text-gray-400" />
              </div>
            </div>

            <!-- Variant Images -->
            <div
              v-if="variantImages.length > 1"
              class="flex gap-2 mt-4 overflow-x-auto"
            >
              <button
                v-for="(img, index) in variantImages"
                :key="img.id"
                class="w-20 h-20 rounded-lg overflow-hidden border-2 shrink-0"
                :class="
                  selectedImageIndex === index
                    ? 'border-primary'
                    : 'border-transparent'
                "
                @click="selectedImageIndex = index"
              >
                <VSecureImage
                  :file-id="img.file_id"
                  :alt="`${product.name} - ${index + 1}`"
                  width="80"
                  height="80"
                  object-fit="cover"
                />
              </button>
            </div>
          </div>

          <!-- Product Info -->
          <div class="w-1/2">
            <div class="mb-4">
              <UBadge v-if="product.brand" variant="soft" class="mb-2">
                {{ product.brand.name }}
              </UBadge>
              <h1 class="text-3xl font-bold mb-2">{{ product.name }}</h1>
              <p v-if="product.short_description" class="text-dimmed">
                {{ product.short_description }}
              </p>
            </div>

            <!-- Price -->
            <div class="text-3xl font-bold text-primary mb-6">
              {{
                formatPrice(
                  currentVariant?.current_price ||
                    product.current_price ||
                    product.base_price
                )
              }}
              грн
            </div>

            <!-- Variant Selection -->
            <div
              v-if="product.attributes && product.attributes.length"
              class="mb-6"
            >
              <div
                v-for="attribute in product.attributes"
                :key="attribute.id"
                class="mb-4"
              >
                <h4 class="font-semibold mb-2">{{ attribute.name }}</h4>
                <div class="flex flex-wrap gap-2">
                  <UButton
                    v-for="value in attribute.values"
                    :key="value.id"
                    :variant="
                      isAttributeSelected(attribute.id, value.id)
                        ? 'solid'
                        : 'outline'
                    "
                    size="sm"
                    :disabled="
                      !isAttributeValueAvailable(attribute.id, value.id)
                    "
                    :class="{
                      'line-through opacity-50': !isAttributeValueAvailable(
                        attribute.id,
                        value.id
                      ),
                    }"
                    @click="selectAttribute(attribute.id, value.id)"
                  >
                    <span
                      v-if="value.color_code"
                      class="w-4 h-4 rounded-full mr-2"
                      :style="{ backgroundColor: value.color_code }"
                    />
                    {{ value.value }}
                  </UButton>
                </div>
              </div>
            </div>

            <!-- Stock Status -->
            <div class="mb-6">
              <UBadge
                :variant="isInStock ? 'soft' : 'subtle'"
                :color="isInStock ? 'success' : 'error'"
              >
                {{ isInStock ? "В наявності" : "Немає в наявності" }}
              </UBadge>
              <span
                v-if="currentVariant && isInStock"
                class="text-sm text-dimmed ml-2"
              >
                ({{ currentVariant.stock }} шт.)
              </span>
            </div>

            <!-- Add to Cart -->
            <div class="flex gap-4">
              <UButton size="lg" :disabled="!isInStock">
                Додати в кошик
              </UButton>
              <UButton size="lg" variant="outline"> Додати в обране </UButton>
            </div>

            <!-- SKU -->
            <div v-if="currentVariant?.sku" class="mt-6 text-sm text-dimmed">
              Артикул: {{ currentVariant.sku }}
            </div>
          </div>
        </div>

        <!-- Description -->
        <div v-if="product.description" class="mt-12">
          <h2 class="text-2xl font-bold mb-4">Опис</h2>
          <div class="prose max-w-none" v-html="product.description" />
        </div>

        <!-- Specifications -->
        <div
          v-if="product.specifications && product.specifications.length"
          class="mt-12"
        >
          <h2 class="text-2xl font-bold mb-4">Характеристики</h2>
          <div class="grid gap-2">
            <div
              v-for="spec in product.specifications"
              :key="spec.id"
              class="flex py-2 border-b"
            >
              <span class="w-1/3">{{ spec.name }}</span>
              <span class="w-2/3 text-dimmed">{{ spec.value }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Not Found -->
      <div v-else-if="hasFetched && !product" class="text-center py-12">
        <Package class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h3 class="text-xl font-semibold mb-2">Товар не знайдено</h3>
        <p class="text-dimmed mb-4">
          Можливо, він був видалений або переміщений
        </p>
        <UButton to="/category" variant="soft">
          Повернутись до каталогу
        </UButton>
      </div>
    </UContainer>
  </UPage>
</template>

<script setup lang="ts">
import { Package } from "lucide-vue-next";
import VBreadcrumbs from "~/components/common/VBreadcrumbs.vue";
import VSecureImage from "~/components/common/VSecureImage.vue";
import { buildProductUrl } from "~/utils/urlBuilder";
import type {
  Product,
  ProductVariant,
  ProductVariantImage,
} from "~/models/product";

const route = useRoute();
const router = useRouter();
const client = useSanctumClient();

// Get slug array from route
const slugArray = computed(() => {
  const slug = route.params.slug;
  if (Array.isArray(slug)) return slug;
  if (slug) return [slug];
  return [];
});

// Product data
const product = ref<Product | null>(null);
const isLoading = ref(true);
const hasFetched = ref(false);
const breadcrumbs = ref<Array<{ label: string; to?: string }>>([]);

// Variant selection
const selectedAttributes = ref<Record<number, number>>({});
const selectedImageIndex = ref(0);

// Current variant based on selected attributes
const currentVariant = computed<ProductVariant | null>(() => {
  if (!product.value?.variants?.length) return null;

  // Find variant matching selected attributes
  const selectedAttrValues = Object.values(selectedAttributes.value);
  if (selectedAttrValues.length === 0) {
    // Return default variant
    return (
      product.value.variants.find((v) => v.is_default) ??
      product.value.variants[0] ??
      null
    );
  }

  return (
    product.value.variants.find((variant) => {
      if (!variant.attribute_values) return false;
      const variantAttrIds = variant.attribute_values.map((av) => av.id);
      return selectedAttrValues.every((attrId) =>
        variantAttrIds.includes(attrId)
      );
    }) ??
    product.value.variants[0] ??
    null
  );
});

// Variant images
const variantImages = computed<ProductVariantImage[]>(() => {
  return currentVariant.value?.images || [];
});

// Main image file ID for VSecureImage
const mainImageFileId = computed<number | null>(() => {
  if (variantImages.value.length > 0) {
    return variantImages.value[selectedImageIndex.value]?.file_id ?? null;
  }
  return product.value?.main_image_file_id ?? null;
});

// Stock status
const isInStock = computed(() => {
  if (currentVariant.value) {
    return currentVariant.value.stock > 0;
  }
  return false;
});

// Attribute selection
const isAttributeSelected = (attrId: number, valueId: number): boolean => {
  return selectedAttributes.value[attrId] === valueId;
};

// Check if attribute value is available (has at least one variant with current selection)
const isAttributeValueAvailable = (
  attrId: number,
  valueId: number
): boolean => {
  if (!product.value?.variants?.length) return false;

  // Create a test selection with this attribute value
  const testSelection = { ...selectedAttributes.value, [attrId]: valueId };
  const testAttrValues = Object.values(testSelection);

  // Check if any variant matches this selection
  return product.value.variants.some((variant) => {
    if (!variant.attribute_values) return false;
    const variantAttrIds = variant.attribute_values.map((av) => av.id);
    return testAttrValues.every((attrValueId) =>
      variantAttrIds.includes(attrValueId)
    );
  });
};

const selectAttribute = (attrId: number, valueId: number) => {
  // Update local selection
  const newSelection = { ...selectedAttributes.value, [attrId]: valueId };

  // Find variant matching the new selection
  const selectedAttrValues = Object.values(newSelection);
  const matchingVariant = product.value?.variants?.find((variant) => {
    if (!variant.attribute_values) return false;
    const variantAttrIds = variant.attribute_values.map((av) => av.id);
    return selectedAttrValues.every((attrValueId) =>
      variantAttrIds.includes(attrValueId)
    );
  });

  if (matchingVariant?.slug) {
    // Navigate to variant URL
    const categoryPath = buildCategoryPath();
    const newUrl = buildProductUrl(
      categoryPath,
      product.value!.slug,
      matchingVariant.slug
    );
    router.push(newUrl);
  } else {
    // No matching variant found, just update local state
    selectedAttributes.value = newSelection;
    selectedImageIndex.value = 0;
  }
};

// Build category path array from product category
const buildCategoryPath = (): string[] => {
  if (!product.value?.category) return [];

  const path: string[] = [];
  let currentCategory: typeof product.value.category | undefined =
    product.value.category;

  while (currentCategory) {
    path.unshift(currentCategory.slug);
    currentCategory = currentCategory.parent;
  }

  return path;
};

// Helper functions
const formatPrice = (price: string | number): string => {
  const num = typeof price === "string" ? parseFloat(price) : price;
  return num.toLocaleString("uk-UA");
};

// Fetch product
const fetchProduct = async () => {
  const currentSlugArray = slugArray.value;

  if (currentSlugArray.length === 0) {
    isLoading.value = false;
    hasFetched.value = true;
    return;
  }

  isLoading.value = true;
  hasFetched.value = false;

  // Try to find the product by testing slugs from the end of the URL
  // This handles URLs like: /product/computers/pc-for-designer/apple-imac-24-m4/pink-128gb
  // where we need to find which segment is the product slug
  let foundProduct: Product | null = null;
  let productSlugIndex = -1;

  // Try each segment from the end, looking for a valid product
  for (let i = currentSlugArray.length - 1; i >= 0 && !foundProduct; i--) {
    const slugToTry = currentSlugArray[i];
    if (!slugToTry) continue;

    try {
      const response = await client<{
        success: boolean;
        data: Product;
      }>(`/api/products/slug/${slugToTry}`);

      if (response.data) {
        foundProduct = response.data;
        productSlugIndex = i;
      }
    } catch {
      // Product not found with this slug, try the previous segment
      continue;
    }
  }

  if (!foundProduct) {
    product.value = null;
    isLoading.value = false;
    hasFetched.value = true;
    return;
  }

  product.value = foundProduct;

  // Determine variant slug (everything after the product slug)
  const variantSlug =
    productSlugIndex < currentSlugArray.length - 1
      ? currentSlugArray[productSlugIndex + 1]
      : null;

  // Set variant selection based on URL or default
  if (product.value.variants?.length) {
    let targetVariant: ProductVariant | undefined;

    // Try to find variant by URL slug
    if (variantSlug) {
      targetVariant = product.value.variants.find(
        (v) => v.slug === variantSlug
      );
    }

    // Fall back to default variant
    if (!targetVariant) {
      targetVariant =
        product.value.variants.find((v) => v.is_default) ||
        product.value.variants[0];
    }

    // Set selected attributes from target variant
    if (targetVariant?.attribute_values) {
      const newSelection: Record<number, number> = {};
      targetVariant.attribute_values.forEach((av) => {
        if (av.attribute_id) {
          newSelection[av.attribute_id] = av.id;
        }
      });
      selectedAttributes.value = newSelection;
    }
  }

  // Reset image index
  selectedImageIndex.value = 0;

  // Build breadcrumbs
  buildBreadcrumbs();
  isLoading.value = false;
  hasFetched.value = true;
};

const buildBreadcrumbs = () => {
  const items: Array<{ label: string; to?: string }> = [
    { label: "Каталог", to: "/category" },
  ];

  // Build category chain from parent to child
  if (product.value?.category) {
    const categoryChain: Array<{ name: string; slug: string }> = [];
    let currentCategory: typeof product.value.category | undefined =
      product.value.category;

    // Collect all categories in reverse order (from child to parent)
    while (currentCategory) {
      categoryChain.unshift({
        name: currentCategory.name,
        slug: currentCategory.slug,
      });
      currentCategory = currentCategory.parent;
    }

    // Build the path progressively
    let path = "";
    categoryChain.forEach((cat) => {
      path = path ? `${path}/${cat.slug}` : cat.slug;
      items.push({
        label: cat.name,
        to: `/category/${path}`,
      });
    });
  }

  items.push({ label: product.value?.name || "" });

  breadcrumbs.value = items;
};

// Watch for route changes
watch(
  () => route.params.slug,
  () => {
    fetchProduct();
  },
  { immediate: true, deep: true }
);

// Page title
useHead({
  title: computed(() => product.value?.name || "Товар"),
});
</script>
