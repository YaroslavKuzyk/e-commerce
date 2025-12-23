<template>
  <UContainer class="py-8">
    <!-- Breadcrumbs -->
    <div class="mb-6">
      <nav class="flex items-center gap-2 text-sm text-gray-500">
        <NuxtLink to="/" class="hover:text-primary">{{
          $t("common.home")
        }}</NuxtLink>
        <ChevronRight class="w-4 h-4" />
        <NuxtLink :to="localePath('/comparison')" class="hover:text-primary">
          {{ $t("comparison.listsTitle") }}
        </NuxtLink>
        <ChevronRight class="w-4 h-4" />
        <span class="text-gray-900 dark:text-white">{{ categoryName }}</span>
      </nav>
    </div>

    <!-- Page Header -->
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8"
    >
      <div class="flex items-center gap-3">
        <NuxtLink :to="localePath('/comparison')">
          <UButton variant="ghost" size="sm" class="!px-2">
            <ArrowLeft class="w-4 h-4" />
          </UButton>
        </NuxtLink>
        <div class="w-1 h-8 bg-primary rounded-full"></div>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ $t("comparison.title") }}
          </h1>
          <p class="text-sm text-gray-500 mt-0.5">
            {{ categoryName }} &bull; {{ products.length }}
            {{ $t("common.products") }}
          </p>
        </div>
      </div>

      <!-- Toggle differences only -->
      <div
        class="flex items-center gap-3 bg-gray-50 dark:bg-gray-800 rounded-lg px-4 py-2"
      >
        <UToggle v-model="showOnlyDifferences" size="sm" />
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center py-16">
      <div class="flex flex-col items-center gap-3">
        <UButton loading variant="ghost" size="xl" />
        <span class="text-sm text-gray-500">{{
          $t("comparison.loading")
        }}</span>
      </div>
    </div>

    <!-- Comparison Table -->
    <div
      v-else-if="products.length > 0"
      class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
    >
      <div class="overflow-x-auto">
        <table class="w-full border-collapse">
          <!-- Product Cards Row -->
          <thead>
            <tr>
              <th
                class="sticky left-0 bg-gray-50 dark:bg-gray-800 z-10 min-w-[160px] w-[160px] p-3 text-left border-b border-r border-gray-200 dark:border-gray-700"
              >
                <span
                  class="text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >{{ $t("comparison.characteristics") }}</span
                >
              </th>
              <th
                v-for="product in products"
                :key="product.id"
                class="min-w-[160px] w-[160px] p-0 border-b border-gray-200 dark:border-gray-700 align-top"
              >
                <!-- Product Card (Compact) -->
                <div class="relative p-3 group">
                  <!-- Remove Button -->
                  <button
                    class="absolute top-2 right-2 w-6 h-6 rounded-full bg-white dark:bg-gray-800 shadow-md flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition-all opacity-0 group-hover:opacity-100 z-10"
                    @click="removeProduct(product.id)"
                  >
                    <X class="w-3 h-3" />
                  </button>

                  <NuxtLink
                    :to="buildProductUrlFromProduct(product)"
                    class="block"
                  >
                    <!-- Image Container (Smaller) -->
                    <div
                      class="relative bg-gray-50 dark:bg-gray-800 rounded-lg p-2 mb-2 aspect-square flex items-center justify-center overflow-hidden"
                    >
                      <VSecureImage
                        v-if="product.main_image_file_id"
                        :fileId="product.main_image_file_id"
                        imgClass="w-full h-full object-contain hover:scale-105 transition-transform duration-300"
                      />
                      <div
                        v-else
                        class="w-full h-full flex items-center justify-center"
                      >
                        <Package class="w-8 h-8 text-gray-300" />
                      </div>
                    </div>

                    <!-- Product Name -->
                    <h3
                      class="text-xs font-medium text-gray-900 dark:text-white text-center line-clamp-2 hover:text-primary transition-colors min-h-[32px]"
                    >
                      {{ product.name }}
                    </h3>
                  </NuxtLink>

                  <!-- Price -->
                  <div class="mt-2 text-center">
                    <div class="text-base font-bold text-primary">
                      {{
                        formatPrice(product.current_price || product.base_price)
                      }}
                      <span class="text-xs font-normal">грн</span>
                    </div>
                    <div
                      v-if="product.discount_price"
                      class="text-xs text-gray-400 line-through"
                    >
                      {{ formatPrice(product.base_price) }} грн
                    </div>
                  </div>

                  <!-- Add to Cart Button -->
                  <div class="mt-2">
                    <UButton
                      block
                      size="xs"
                      :color="
                        cartStore.isInCart(product.id) ? 'primary' : 'neutral'
                      "
                      :variant="
                        cartStore.isInCart(product.id) ? 'solid' : 'outline'
                      "
                      @click.prevent="addToCart(product.id)"
                    >
                      <template #leading>
                        <ShoppingCart class="w-3 h-3" />
                      </template>
                      {{
                        cartStore.isInCart(product.id)
                          ? $t("cart.inCart")
                          : $t("cart.addToCart")
                      }}
                    </UButton>
                  </div>
                </div>
              </th>
            </tr>
          </thead>

          <tbody>
            <!-- Brand Row -->
            <tr
              v-if="hasBrand"
              class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
            >
              <td
                class="sticky left-0 bg-white dark:bg-gray-900 z-10 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border-b border-r border-gray-100 dark:border-gray-800"
              >
                {{ $t("comparison.brand") }}
              </td>
              <td
                v-for="product in products"
                :key="`brand-${product.id}`"
                class="px-3 py-2 text-sm text-center text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800"
              >
                <span class="font-medium">{{
                  product.brand?.name || "—"
                }}</span>
              </td>
            </tr>

            <!-- Variant Attribute Rows (Color, Memory, etc.) -->
            <tr
              v-for="(attr, index) in filteredAttributes"
              :key="`attr-${attr.id}`"
              class="transition-colors"
              :class="[
                attr.hasDifferences
                  ? 'bg-amber-50/50 dark:bg-amber-900/10 hover:bg-amber-50 dark:hover:bg-amber-900/20'
                  : 'hover:bg-gray-50 dark:hover:bg-gray-800/50',
                index % 2 === 0 && !attr.hasDifferences
                  ? 'bg-gray-50/50 dark:bg-gray-800/30'
                  : '',
              ]"
            >
              <td
                class="sticky left-0 z-10 px-3 py-2 text-sm font-medium border-b border-r border-gray-100 dark:border-gray-800"
                :class="
                  attr.hasDifferences
                    ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-200'
                    : 'bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300'
                "
              >
                <div class="flex items-center gap-2">
                  <span>{{ attr.name }}</span>
                  <span
                    v-if="attr.hasDifferences"
                    class="w-1.5 h-1.5 rounded-full bg-amber-500"
                  ></span>
                </div>
              </td>
              <td
                v-for="product in products"
                :key="`attr-${attr.id}-${product.id}`"
                class="px-3 py-2 text-sm text-center border-b border-gray-100 dark:border-gray-800"
              >
                <template v-if="getProductAttributeValue(product, attr.id)">
                  <!-- Color display with circle -->
                  <div
                    v-if="attr.type === 'color'"
                    class="flex items-center justify-center gap-2"
                  >
                    <span
                      class="w-4 h-4 rounded-full border border-gray-300 dark:border-gray-600"
                      :style="{
                        backgroundColor:
                          getProductAttributeValue(product, attr.id)
                            ?.color_code || '#ccc',
                      }"
                    ></span>
                    <span class="text-gray-900 dark:text-white">{{
                      getProductAttributeValue(product, attr.id)?.value
                    }}</span>
                  </div>
                  <span v-else class="text-gray-900 dark:text-white">{{
                    getProductAttributeValue(product, attr.id)?.value
                  }}</span>
                </template>
                <span v-else class="text-gray-300 dark:text-gray-600">—</span>
              </td>
            </tr>

            <!-- Specification Rows -->
            <tr
              v-for="(spec, index) in filteredSpecifications"
              :key="spec.name"
              class="transition-colors"
              :class="[
                spec.hasDifferences
                  ? 'bg-amber-50/50 dark:bg-amber-900/10 hover:bg-amber-50 dark:hover:bg-amber-900/20'
                  : 'hover:bg-gray-50 dark:hover:bg-gray-800/50',
                (index + filteredAttributes.length) % 2 === 0 &&
                !spec.hasDifferences
                  ? 'bg-gray-50/50 dark:bg-gray-800/30'
                  : '',
              ]"
            >
              <td
                class="sticky left-0 z-10 px-3 py-2 text-sm font-medium border-b border-r border-gray-100 dark:border-gray-800"
                :class="
                  spec.hasDifferences
                    ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-200'
                    : 'bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300'
                "
              >
                <div class="flex items-center gap-2">
                  <span>{{ spec.name }}</span>
                  <span
                    v-if="spec.hasDifferences"
                    class="w-1.5 h-1.5 rounded-full bg-amber-500"
                  ></span>
                </div>
              </td>
              <td
                v-for="product in products"
                :key="`spec-${spec.name}-${product.id}`"
                class="px-3 py-2 text-sm text-center border-b border-gray-100 dark:border-gray-800"
              >
                <span
                  v-if="getSpecificationValue(product, spec.name)"
                  class="text-gray-900 dark:text-white"
                >
                  {{ getSpecificationValue(product, spec.name) }}
                </span>
                <span v-else class="text-gray-300 dark:text-gray-600">—</span>
              </td>
            </tr>

            <!-- Empty state -->
            <tr
              v-if="
                filteredAttributes.length === 0 &&
                filteredSpecifications.length === 0
              "
            >
              <td
                :colspan="products.length + 1"
                class="px-3 py-8 text-center text-gray-500"
              >
                <span v-if="showOnlyDifferences">{{
                  $t("comparison.noDifferences")
                }}</span>
                <span v-else>{{ $t("comparison.noAttributes") }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-16">
      <div
        class="w-24 h-24 mx-auto mb-6 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center"
      >
        <Scale class="w-12 h-12 text-gray-400" />
      </div>
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
        {{ $t("comparison.emptyTitle") }}
      </h2>
      <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
        {{ $t("comparison.emptyDescription") }}
      </p>
      <NuxtLink :to="localePath('/store')">
        <UButton size="lg">
          <template #leading>
            <ShoppingBag class="w-5 h-5" />
          </template>
          {{ $t("comparison.goToStore") }}
        </UButton>
      </NuxtLink>
    </div>
  </UContainer>
</template>

<script lang="ts" setup>
import {
  Scale,
  ChevronRight,
  ShoppingBag,
  ShoppingCart,
  X,
  Package,
  ArrowLeft,
} from "lucide-vue-next";
import { useComparisonStore } from "~/stores/useComparisonStore";
import { useCartStore } from "~/stores/useCartStore";
import type { Product, AttributeValue } from "~/models/product";
import VSecureImage from "~/components/common/VSecureImage.vue";
import { buildProductUrl } from "~/utils/urlBuilder";

interface SpecificationInfo {
  name: string;
  hasDifferences: boolean;
}

interface AttributeInfo {
  id: number;
  name: string;
  type: string;
  hasDifferences: boolean;
}

const route = useRoute();
const { t } = useI18n();
const localePath = useLocalePath();
const comparisonStore = useComparisonStore();
const cartStore = useCartStore();
const client = useSanctumClient();
const { user } = useSanctumAuth();

const slug = computed(() => route.params.slug as string);
const isAuthenticated = computed(() => !!user.value);
const isLoading = ref(true);
const products = ref<Product[]>([]);
const categoryName = ref("");
const showOnlyDifferences = ref(false);

// Extract all unique attributes from product variants
const allAttributes = computed<AttributeInfo[]>(() => {
  const attrMap = new Map<
    number,
    { name: string; type: string; values: Set<string> }
  >();

  for (const product of products.value) {
    if (!product.variants) continue;

    for (const variant of product.variants) {
      if (!variant.attribute_values) continue;

      for (const attrValue of variant.attribute_values) {
        if (!attrValue.attribute) continue;

        const attrId = attrValue.attribute.id;
        if (!attrMap.has(attrId)) {
          attrMap.set(attrId, {
            name: attrValue.attribute.name,
            type: attrValue.attribute.type,
            values: new Set(),
          });
        }
        attrMap.get(attrId)!.values.add(attrValue.value);
      }
    }
  }

  return Array.from(attrMap.entries())
    .map(([id, data]) => ({
      id,
      name: data.name,
      type: data.type,
      hasDifferences: data.values.size > 1,
    }))
    .sort((a, b) => a.name.localeCompare(b.name, "uk"));
});

// Filter attributes based on showOnlyDifferences
const filteredAttributes = computed(() => {
  if (showOnlyDifferences.value) {
    return allAttributes.value.filter((attr) => attr.hasDifferences);
  }
  return allAttributes.value;
});

// Get attribute value for a product by attribute ID
const getProductAttributeValue = (
  product: Product,
  attributeId: number
): AttributeValue | null => {
  if (!product.variants) return null;

  for (const variant of product.variants) {
    if (!variant.attribute_values) continue;

    const attrValue = variant.attribute_values.find(
      (av) => av.attribute?.id === attributeId
    );
    if (attrValue) return attrValue;
  }
  return null;
};

// Extract all specifications from products
const allSpecifications = computed<SpecificationInfo[]>(() => {
  const specMap = new Map<string, Set<string>>();

  for (const product of products.value) {
    if (!product.specifications) continue;

    for (const spec of product.specifications) {
      if (!specMap.has(spec.name)) {
        specMap.set(spec.name, new Set());
      }
      specMap.get(spec.name)!.add(spec.value);
    }
  }

  // Sort by specification name
  return Array.from(specMap.entries())
    .map(([name, values]) => ({
      name,
      hasDifferences: values.size > 1,
    }))
    .sort((a, b) => a.name.localeCompare(b.name, "uk"));
});

// Filter specifications based on showOnlyDifferences
const filteredSpecifications = computed(() => {
  if (showOnlyDifferences.value) {
    return allSpecifications.value.filter((spec) => spec.hasDifferences);
  }
  return allSpecifications.value;
});

// Check if any product has brand
const hasBrand = computed(() => {
  return products.value.some((p) => p.brand?.name);
});

// Initialize
onMounted(async () => {
  await comparisonStore.init();
  await loadProducts();
  isLoading.value = false;
});

// Load products for comparison
const loadProducts = async () => {
  if (isAuthenticated.value) {
    await loadAuthProducts();
  } else {
    await loadGuestProducts();
  }
};

// Load products for authenticated users
const loadAuthProducts = async () => {
  try {
    const response = await client<{
      success: boolean;
      data: {
        category: { id: number; name: string; slug: string } | null;
        products: Product[];
      };
    }>(`/api/comparisons/category/${slug.value}`);

    categoryName.value = response.data.category?.name || "";
    products.value = response.data.products || [];
  } catch (error) {
    console.error("Failed to load comparison products:", error);
  }
};

// Load products for guest users
const loadGuestProducts = async () => {
  // Find category ID by slug and get product IDs
  const productIds = Array.from(comparisonStore.comparisonItems.keys());
  if (productIds.length === 0) return;

  try {
    const idsParam = productIds.join(",");
    const response = await client<{ success: boolean; data: Product[] }>(
      `/api/products?ids=${idsParam}&limit=50`
    );

    // Filter products by category slug
    const filtered = (response.data || []).filter((product) => {
      let currentCategory = product.category;
      while (currentCategory?.parent) {
        currentCategory = currentCategory.parent;
      }
      return currentCategory?.slug === slug.value;
    });

    products.value = filtered;

    // Set category name from first product
    if (filtered.length > 0) {
      let rootCategory = filtered[0].category;
      while (rootCategory?.parent) {
        rootCategory = rootCategory.parent;
      }
      categoryName.value = rootCategory?.name || "";
    }
  } catch (error) {
    console.error("Failed to load guest comparison products:", error);
  }
};

// Get specification value for product
const getSpecificationValue = (
  product: Product,
  specName: string
): string | null => {
  if (!product.specifications) return null;
  const spec = product.specifications.find((s) => s.name === specName);
  return spec?.value || null;
};

// Build product URL
const buildProductUrlFromProduct = (product: Product) => {
  const path: string[] = [];
  let currentCategory = product.category;

  while (currentCategory) {
    path.unshift(currentCategory.slug);
    currentCategory = currentCategory.parent;
  }

  return buildProductUrl(path, product.slug);
};

// Format price
const formatPrice = (price: string | number) => {
  return Number(price).toLocaleString("uk-UA");
};

// Remove product from comparison
const removeProduct = async (productId: number) => {
  await comparisonStore.remove(productId);
  products.value = products.value.filter((p) => p.id !== productId);

  // Redirect if no products left
  if (products.value.length === 0) {
    navigateTo(localePath("/comparison"));
  }
};

// Add to cart
const addToCart = async (productId: number) => {
  await cartStore.add(productId);
};

// SEO
useHead({
  title: computed(() => `${t("comparison.title")} - ${categoryName.value}`),
});
</script>
