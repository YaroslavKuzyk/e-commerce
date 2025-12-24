<template>
  <div class="v-product-filters-slug flex flex-col gap-6">
    <!-- Price Range -->
    <div v-if="availableFilters?.price_range">
      <h4 class="font-semibold mb-3">Ціна</h4>
      <div class="flex gap-2 items-center">
        <UInput
          v-model.number="localFilters.priceMin"
          type="number"
          :placeholder="`від ${availableFilters.price_range.min}`"
          class="w-24"
          @update:model-value="debouncedEmit"
        />
        <span>-</span>
        <UInput
          v-model.number="localFilters.priceMax"
          type="number"
          :placeholder="`до ${availableFilters.price_range.max}`"
          class="w-24"
          @update:model-value="debouncedEmit"
        />
        <span class="text-sm text-dimmed">грн</span>
      </div>
    </div>

    <!-- Brands -->
    <div v-if="availableFilters?.brands?.length">
      <h4 class="font-semibold mb-3">Бренди</h4>
      <div class="flex flex-col gap-2 max-h-48 overflow-y-auto">
        <UCheckbox
          v-for="brand in availableFilters.brands"
          :key="brand.id"
          :model-value="isBrandSelected(brand.slug)"
          :label="`${brand.name} (${brand.products_count})`"
          @update:model-value="(val: boolean) => toggleBrand(brand.slug, val)"
        />
      </div>
    </div>

    <!-- Attributes -->
    <div v-for="attribute in availableFilters?.attributes" :key="attribute.id">
      <h4 class="font-semibold mb-3">{{ attribute.name }}</h4>
      <div class="flex flex-col gap-2 max-h-48 overflow-y-auto">
        <UCheckbox
          v-for="value in attribute.values"
          :key="value.id"
          :model-value="isAttributeValueSelected(attribute.slug, value.slug)"
          :label="`${value.value} (${value.variants_count})`"
          @update:model-value="(val: boolean) => toggleAttributeValue(attribute.slug, value.slug, val)"
        />
      </div>
    </div>

    <!-- Special Filters -->
    <div class="flex flex-col gap-2">
      <UCheckbox
        v-model="localFilters.inStock"
        label="Тільки в наявності"
        @update:model-value="emitFilters"
      />
      <UCheckbox
        v-model="localFilters.hasDiscount"
        label="Акції"
        @update:model-value="emitFilters"
      />
      <UCheckbox
        v-model="localFilters.isClearance"
        label="Уцінка"
        @update:model-value="emitFilters"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, watch } from "vue";
import { useDebounceFn } from "@vueuse/core";
import type { ParsedFilters } from "~/utils/urlParser";
import type { AvailableFilters } from "~/models/product";

interface Props {
  filters: ParsedFilters;
  availableFilters?: AvailableFilters | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  "update:filters": [filters: ParsedFilters];
}>();

// Local copy of filters
const localFilters = ref<ParsedFilters>({
  ...props.filters,
  brandSlugs: props.filters.brandSlugs || [],
  attributeFilters: props.filters.attributeFilters || {},
});

// Watch for external changes
watch(
  () => props.filters,
  (newVal) => {
    localFilters.value = {
      ...newVal,
      brandSlugs: newVal.brandSlugs || [],
      attributeFilters: newVal.attributeFilters || {},
    };
  },
  { deep: true }
);

// Check if brand is selected
const isBrandSelected = (brandSlug: string): boolean => {
  return Array.isArray(localFilters.value.brandSlugs) &&
    localFilters.value.brandSlugs.includes(brandSlug);
};

// Check if attribute value is selected
const isAttributeValueSelected = (attrSlug: string, valueSlug: string): boolean => {
  const attrFilters = localFilters.value.attributeFilters;
  if (!attrFilters || !attrFilters[attrSlug]) return false;
  return attrFilters[attrSlug].includes(valueSlug);
};

const emitFilters = () => {
  // Clean up empty values before emitting
  const cleanFilters: ParsedFilters = {};

  if (localFilters.value.brandSlugs && localFilters.value.brandSlugs.length > 0) {
    cleanFilters.brandSlugs = localFilters.value.brandSlugs;
  }

  if (localFilters.value.attributeFilters) {
    const cleanAttrFilters: Record<string, string[]> = {};
    for (const [attrSlug, values] of Object.entries(localFilters.value.attributeFilters)) {
      if (values && values.length > 0) {
        cleanAttrFilters[attrSlug] = values;
      }
    }
    if (Object.keys(cleanAttrFilters).length > 0) {
      cleanFilters.attributeFilters = cleanAttrFilters;
    }
  }

  if (localFilters.value.priceMin !== undefined && localFilters.value.priceMin !== null) {
    cleanFilters.priceMin = localFilters.value.priceMin;
  }

  if (localFilters.value.priceMax !== undefined && localFilters.value.priceMax !== null) {
    cleanFilters.priceMax = localFilters.value.priceMax;
  }

  if (localFilters.value.inStock) {
    cleanFilters.inStock = localFilters.value.inStock;
  }

  if (localFilters.value.hasDiscount) {
    cleanFilters.hasDiscount = localFilters.value.hasDiscount;
  }

  if (localFilters.value.isClearance) {
    cleanFilters.isClearance = localFilters.value.isClearance;
  }

  if (localFilters.value.sortBy) {
    cleanFilters.sortBy = localFilters.value.sortBy;
  }

  emit("update:filters", cleanFilters);
};

const debouncedEmit = useDebounceFn(emitFilters, 500);

const toggleBrand = (brandSlug: string, checked: boolean) => {
  if (!localFilters.value.brandSlugs) {
    localFilters.value.brandSlugs = [];
  }

  if (checked) {
    localFilters.value.brandSlugs.push(brandSlug);
  } else {
    localFilters.value.brandSlugs = localFilters.value.brandSlugs.filter(
      (slug) => slug !== brandSlug
    );
  }

  emitFilters();
};

const toggleAttributeValue = (attrSlug: string, valueSlug: string, checked: boolean) => {
  if (!localFilters.value.attributeFilters) {
    localFilters.value.attributeFilters = {};
  }

  if (!localFilters.value.attributeFilters[attrSlug]) {
    localFilters.value.attributeFilters[attrSlug] = [];
  }

  if (checked) {
    localFilters.value.attributeFilters[attrSlug].push(valueSlug);
  } else {
    localFilters.value.attributeFilters[attrSlug] = localFilters.value.attributeFilters[attrSlug].filter(
      (slug) => slug !== valueSlug
    );
  }

  emitFilters();
};
</script>
