<template>
  <div class="flex flex-wrap gap-2 mb-4">
    <UBadge
      v-if="searchQuery"
      variant="soft"
      class="cursor-pointer"
      @click="$emit('remove-search')"
    >
      Пошук: {{ searchQuery }}
      <X class="w-3 h-3 ml-1" />
    </UBadge>

    <UBadge
      v-for="brandSlug in filters.brandSlugs"
      :key="`brand-${brandSlug}`"
      variant="soft"
      class="cursor-pointer"
      @click="$emit('remove-brand', brandSlug)"
    >
      {{ getBrandName(brandSlug) }}
      <X class="w-3 h-3 ml-1" />
    </UBadge>

    <UBadge
      v-if="filters.priceMin || filters.priceMax"
      variant="soft"
      class="cursor-pointer"
      @click="$emit('remove-price')"
    >
      Ціна: {{ filters.priceMin || 0 }} - {{ filters.priceMax || "..." }} грн
      <X class="w-3 h-3 ml-1" />
    </UBadge>

    <template
      v-for="(values, attrSlug) in filters.attributeFilters"
      :key="`attr-${attrSlug}`"
    >
      <UBadge
        v-for="valueSlug in values"
        :key="`${attrSlug}-${valueSlug}`"
        variant="soft"
        class="cursor-pointer"
        @click="$emit('remove-attribute', attrSlug as string, valueSlug)"
      >
        {{ getAttributeValueName(attrSlug as string, valueSlug) }}
        <X class="w-3 h-3 ml-1" />
      </UBadge>
    </template>

    <UBadge
      v-if="filters.inStock"
      variant="soft"
      class="cursor-pointer"
      @click="$emit('remove-in-stock')"
    >
      В наявності
      <X class="w-3 h-3 ml-1" />
    </UBadge>
  </div>
</template>

<script setup lang="ts">
import { X } from "lucide-vue-next";
import type { ParsedFilters } from "~/utils/urlParser";
import type { AvailableFilters } from "~/models/product";

interface Props {
  searchQuery: string;
  filters: ParsedFilters;
  availableFilters: AvailableFilters | null;
}

const props = defineProps<Props>();

defineEmits<{
  "remove-search": [];
  "remove-brand": [brandSlug: string];
  "remove-price": [];
  "remove-attribute": [attrSlug: string, valueSlug: string];
  "remove-in-stock": [];
}>();

const getBrandName = (brandSlug: string): string =>
  props.availableFilters?.brands.find((b) => b.slug === brandSlug)?.name ||
  brandSlug;

const getAttributeValueName = (attrSlug: string, valueSlug: string): string => {
  const attr = props.availableFilters?.attributes.find(
    (a) => a.slug === attrSlug
  );
  if (!attr) return valueSlug;

  const value = attr.values.find((v) => v.slug === valueSlug);
  return value ? `${attr.name}: ${value.value}` : `${attrSlug}: ${valueSlug}`;
};
</script>
