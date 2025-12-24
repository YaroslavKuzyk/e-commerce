<template>
  <div class="v-product-filters flex flex-col gap-6">
    <!-- Price Range -->
    <div v-if="availableFilters?.price_range">
      <h4 class="font-semibold mb-3">Ціна</h4>
      <div class="flex gap-2 items-center">
        <UInput
          v-model.number="localFilters.price_min"
          type="number"
          :placeholder="`від ${availableFilters.price_range.min}`"
          class="w-24"
          @update:model-value="debouncedEmit"
        />
        <span>-</span>
        <UInput
          v-model.number="localFilters.price_max"
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
          :model-value="isBrandSelected(brand.id)"
          :label="`${brand.name} (${brand.products_count})`"
          @update:model-value="(val: boolean) => toggleBrand(brand.id, val)"
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
          :model-value="isAttributeValueSelected(value.id)"
          :label="`${value.value} (${value.variants_count})`"
          @update:model-value="(val: boolean) => toggleAttributeValue(value.id, val)"
        />
      </div>
    </div>

    <!-- Specifications -->
    <div v-for="spec in availableFilters?.specifications" :key="spec.name">
      <h4 class="font-semibold mb-3">{{ spec.name }}</h4>
      <USelectMenu
        v-model="localFilters.specifications![spec.name]"
        :items="spec.values"
        placeholder="Оберіть значення"
        :ui="{ base: 'w-full' }"
        @update:model-value="emitFilters"
      />
    </div>

    <!-- Special Filters -->
    <div class="flex flex-col gap-2">
      <UCheckbox
        v-model="localFilters.in_stock"
        label="Тільки в наявності"
        @update:model-value="emitFilters"
      />
      <UCheckbox
        v-model="localFilters.has_discount"
        label="Акції"
        @update:model-value="emitFilters"
      />
      <UCheckbox
        v-model="localFilters.is_clearance"
        label="Уцінка"
        @update:model-value="emitFilters"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, watch } from "vue";
import { useDebounceFn } from "@vueuse/core";
import type { ProductFilters, AvailableFilters } from "~/models/product";

interface Props {
  modelValue: ProductFilters;
  availableFilters?: AvailableFilters | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  "update:modelValue": [filters: ProductFilters];
}>();

// Check if brand is selected
const isBrandSelected = (brandId: number): boolean => {
  return Array.isArray(localFilters.value.brand_ids) && localFilters.value.brand_ids.includes(brandId);
};

// Check if attribute value is selected
const isAttributeValueSelected = (valueId: number): boolean => {
  return Array.isArray(localFilters.value.attribute_values) && localFilters.value.attribute_values.includes(valueId);
};

const localFilters = ref<ProductFilters>({
  ...props.modelValue,
  brand_ids: props.modelValue.brand_ids || [],
  attribute_values: props.modelValue.attribute_values || [],
  specifications: props.modelValue.specifications || {},
});

// Watch for external changes
watch(
  () => props.modelValue,
  (newVal) => {
    localFilters.value = {
      ...newVal,
      brand_ids: newVal.brand_ids || [],
      attribute_values: newVal.attribute_values || [],
      specifications: newVal.specifications || {},
    };
  },
  { deep: true }
);

const emitFilters = () => {
  // Clean up empty values before emitting
  const cleanFilters: ProductFilters = {};

  if (localFilters.value.search) {
    cleanFilters.search = localFilters.value.search;
  }
  if (localFilters.value.category_id) {
    cleanFilters.category_id = localFilters.value.category_id;
  }
  if (localFilters.value.brand_ids && localFilters.value.brand_ids.length > 0) {
    cleanFilters.brand_ids = localFilters.value.brand_ids;
  }
  if (localFilters.value.price_min !== undefined && localFilters.value.price_min !== null) {
    cleanFilters.price_min = localFilters.value.price_min;
  }
  if (localFilters.value.price_max !== undefined && localFilters.value.price_max !== null) {
    cleanFilters.price_max = localFilters.value.price_max;
  }
  if (localFilters.value.attribute_values && localFilters.value.attribute_values.length > 0) {
    cleanFilters.attribute_values = localFilters.value.attribute_values;
  }
  if (localFilters.value.specifications && Object.keys(localFilters.value.specifications).length > 0) {
    cleanFilters.specifications = localFilters.value.specifications;
  }
  if (localFilters.value.in_stock) {
    cleanFilters.in_stock = localFilters.value.in_stock;
  }
  if (localFilters.value.has_discount) {
    cleanFilters.has_discount = localFilters.value.has_discount;
  }
  if (localFilters.value.is_clearance) {
    cleanFilters.is_clearance = localFilters.value.is_clearance;
  }
  if (localFilters.value.sort_by) {
    cleanFilters.sort_by = localFilters.value.sort_by;
  }

  emit("update:modelValue", cleanFilters);
};

const debouncedEmit = useDebounceFn(emitFilters, 500);

const toggleBrand = (brandId: number, checked: boolean) => {
  if (!localFilters.value.brand_ids) {
    localFilters.value.brand_ids = [];
  }

  if (checked) {
    localFilters.value.brand_ids.push(brandId);
  } else {
    localFilters.value.brand_ids = localFilters.value.brand_ids.filter(
      (id) => id !== brandId
    );
  }

  emitFilters();
};

const toggleAttributeValue = (valueId: number, checked: boolean) => {
  if (!localFilters.value.attribute_values) {
    localFilters.value.attribute_values = [];
  }

  if (checked) {
    localFilters.value.attribute_values.push(valueId);
  } else {
    localFilters.value.attribute_values = localFilters.value.attribute_values.filter(
      (id) => id !== valueId
    );
  }

  emitFilters();
};
</script>
