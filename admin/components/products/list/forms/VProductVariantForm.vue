<template>
  <div class="space-y-4 p-4">
    <UFormField label="SKU" name="sku">
      <UInput
        v-model="state.sku"
        placeholder="PROD-001-RED-XL"
        class="w-full"
      />
    </UFormField>

    <UFormField label="Slug" name="slug">
      <UInput
        v-model="state.slug"
        placeholder="red-xl"
        class="w-full"
      />
    </UFormField>

    <UFormField label="Назва варіації (опціонально)" name="name">
      <UInput
        v-model="state.name"
        placeholder="Червоний XL"
        class="w-full"
      />
    </UFormField>

    <div class="grid grid-cols-2 gap-4">
      <UFormField label="Ціна" name="price">
        <UInput
          v-model.number="state.price"
          type="number"
          step="0.01"
          placeholder="0.00"
          class="w-full"
        />
      </UFormField>

      <UFormField label="Залишок" name="stock">
        <UInput
          v-model.number="state.stock"
          type="number"
          placeholder="0"
          class="w-full"
        />
      </UFormField>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <UFormField label="Статус" name="status">
        <USelect
          v-model="state.status"
          :items="statusItems"
          class="w-full"
        />
      </UFormField>

      <UFormField label="За замовчуванням" name="is_default">
        <div class="flex items-center h-full pt-2">
          <UCheckbox v-model="state.is_default" label="Встановити як варіацію за замовчуванням" />
        </div>
      </UFormField>
    </div>

    <!-- Attribute Values -->
    <div v-if="product.attributes.length > 0" class="space-y-3">
      <h4 class="font-medium">Значення атрибутів</h4>
      <div
        v-for="attr in product.attributes"
        :key="attr.id"
        class="flex items-center gap-4"
      >
        <span class="w-24 text-sm text-gray-600">{{ attr.name }}:</span>
        <USelectMenu
          v-model="selectedAttributeValues[attr.id]"
          :items="getAttributeValueOptions(attr)"
          placeholder="Виберіть значення"
          value-key="value"
          label-key="label"
          class="flex-1"
          :multiple="attr.type === 'multi_select'"
        />
      </div>
    </div>

    <!-- Pricing Override Section -->
    <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
      <button
        type="button"
        class="w-full flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors"
        @click="state.override_pricing = !state.override_pricing"
      >
        <div class="flex items-center gap-3">
          <UCheckbox v-model="state.override_pricing" @click.stop />
          <div class="flex items-center gap-2">
            <Settings class="w-5 h-5 text-gray-600" />
            <span class="font-medium text-gray-800 dark:text-gray-200">Власні налаштування цін</span>
          </div>
        </div>
        <ChevronDown
          class="w-5 h-5 text-gray-600 transition-transform"
          :class="{ 'rotate-180': state.override_pricing }"
        />
      </button>

      <div v-if="state.override_pricing" class="p-4 space-y-4 bg-gray-50/50 dark:bg-gray-800/30">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Якщо увімкнено, ця варіація матиме власні налаштування знижки та уцінки замість батьківського продукту.
        </p>

        <!-- Discount Section -->
        <div class="rounded-lg border border-amber-200 dark:border-amber-800 overflow-hidden">
          <button
            type="button"
            class="w-full flex items-center justify-between p-3 bg-amber-50 dark:bg-amber-950/20 hover:bg-amber-100 dark:hover:bg-amber-950/30 transition-colors"
            @click="hasDiscount = !hasDiscount"
          >
            <div class="flex items-center gap-3">
              <UCheckbox v-model="hasDiscount" @click.stop />
              <div class="flex items-center gap-2">
                <Percent class="w-4 h-4 text-amber-600" />
                <span class="font-medium text-amber-800 dark:text-amber-200 text-sm">Знижка</span>
              </div>
            </div>
            <ChevronDown
              class="w-4 h-4 text-amber-600 transition-transform"
              :class="{ 'rotate-180': hasDiscount }"
            />
          </button>

          <div v-if="hasDiscount" class="p-3 space-y-3 bg-amber-50/50 dark:bg-amber-950/10">
            <div class="grid grid-cols-2 gap-3">
              <UFormField label="Ціна зі знижкою" name="discount_price">
                <UInput
                  v-model.number="state.discount_price"
                  type="number"
                  step="0.01"
                  placeholder="0.00"
                  class="w-full"
                  @input="onDiscountPriceChange"
                />
              </UFormField>

              <UFormField label="Відсоток знижки (%)" name="discount_percent">
                <UInput
                  v-model.number="state.discount_percent"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  placeholder="0"
                  class="w-full"
                  @input="onDiscountPercentChange"
                />
              </UFormField>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <UFormField label="Початок знижки" name="discount_starts_at">
                <div class="relative">
                  <UButton
                    type="button"
                    variant="outline"
                    color="neutral"
                    size="sm"
                    class="w-full justify-start"
                    @click="isDiscountStartPickerOpen = !isDiscountStartPickerOpen"
                  >
                    <template #leading>
                      <Calendar class="w-3 h-3" />
                    </template>
                    {{ formatDateForDisplay(state.discount_starts_date, state.discount_starts_hours, state.discount_starts_minutes) }}
                  </UButton>
                  <div
                    v-if="isDiscountStartPickerOpen"
                    class="absolute left-0 top-full mt-1 z-50 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-3"
                  >
                    <UCalendar v-model="state.discount_starts_date" />
                    <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                      <span class="text-sm text-gray-500">Час:</span>
                      <USelectMenu
                        v-model="state.discount_starts_hours"
                        :items="hoursOptions"
                        value-key="value"
                        label-key="label"
                        class="w-20"
                      />
                      <span>:</span>
                      <USelectMenu
                        v-model="state.discount_starts_minutes"
                        :items="minutesOptions"
                        value-key="value"
                        label-key="label"
                        class="w-20"
                      />
                    </div>
                    <div class="flex justify-end gap-2 mt-3">
                      <UButton
                        type="button"
                        size="xs"
                        variant="ghost"
                        color="neutral"
                        @click="state.discount_starts_date = null; isDiscountStartPickerOpen = false"
                      >
                        Очистити
                      </UButton>
                      <UButton
                        type="button"
                        size="xs"
                        @click="isDiscountStartPickerOpen = false"
                      >
                        Готово
                      </UButton>
                    </div>
                  </div>
                </div>
              </UFormField>

              <UFormField label="Кінець знижки" name="discount_ends_at">
                <div class="relative">
                  <UButton
                    type="button"
                    variant="outline"
                    color="neutral"
                    size="sm"
                    class="w-full justify-start"
                    @click="isDiscountEndPickerOpen = !isDiscountEndPickerOpen"
                  >
                    <template #leading>
                      <Calendar class="w-3 h-3" />
                    </template>
                    {{ formatDateForDisplay(state.discount_ends_date, state.discount_ends_hours, state.discount_ends_minutes) }}
                  </UButton>
                  <div
                    v-if="isDiscountEndPickerOpen"
                    class="absolute left-0 top-full mt-1 z-50 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-3"
                  >
                    <UCalendar v-model="state.discount_ends_date" />
                    <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                      <span class="text-sm text-gray-500">Час:</span>
                      <USelectMenu
                        v-model="state.discount_ends_hours"
                        :items="hoursOptions"
                        value-key="value"
                        label-key="label"
                        class="w-20"
                      />
                      <span>:</span>
                      <USelectMenu
                        v-model="state.discount_ends_minutes"
                        :items="minutesOptions"
                        value-key="value"
                        label-key="label"
                        class="w-20"
                      />
                    </div>
                    <div class="flex justify-end gap-2 mt-3">
                      <UButton
                        type="button"
                        size="xs"
                        variant="ghost"
                        color="neutral"
                        @click="state.discount_ends_date = null; isDiscountEndPickerOpen = false"
                      >
                        Очистити
                      </UButton>
                      <UButton
                        type="button"
                        size="xs"
                        @click="isDiscountEndPickerOpen = false"
                      >
                        Готово
                      </UButton>
                    </div>
                  </div>
                </div>
              </UFormField>
            </div>

            <div v-if="state.discount_price && state.price" class="text-xs text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/30 p-2 rounded">
              Економія: <strong>{{ (state.price - state.discount_price).toFixed(2) }} грн</strong>
            </div>
          </div>
        </div>

        <!-- Clearance Section -->
        <div class="rounded-lg border border-red-200 dark:border-red-800 overflow-hidden">
          <button
            type="button"
            class="w-full flex items-center justify-between p-3 bg-red-50 dark:bg-red-950/20 hover:bg-red-100 dark:hover:bg-red-950/30 transition-colors"
            @click="state.is_clearance = !state.is_clearance"
          >
            <div class="flex items-center gap-3">
              <UCheckbox v-model="state.is_clearance" @click.stop />
              <div class="flex items-center gap-2">
                <Tag class="w-4 h-4 text-red-600" />
                <span class="font-medium text-red-800 dark:text-red-200 text-sm">Уцінка</span>
              </div>
            </div>
            <ChevronDown
              class="w-4 h-4 text-red-600 transition-transform"
              :class="{ 'rotate-180': state.is_clearance }"
            />
          </button>

          <div v-if="state.is_clearance" class="p-3 space-y-3 bg-red-50/50 dark:bg-red-950/10">
            <UFormField label="Ціна уцінки" name="clearance_price">
              <UInput
                v-model.number="state.clearance_price"
                type="number"
                step="0.01"
                placeholder="0.00"
                class="w-full"
              />
            </UFormField>

            <UFormField label="Причина уцінки" name="clearance_reason">
              <UTextarea
                v-model="state.clearance_reason"
                placeholder="Вкажіть причину уцінки..."
                :rows="2"
                class="w-full"
              />
            </UFormField>

            <div v-if="state.clearance_price && state.price" class="text-xs text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/30 p-2 rounded">
              Знижка: <strong>{{ calculateClearancePercent }}%</strong> від базової ціни
            </div>
          </div>
        </div>
      </div>

      <!-- Parent product settings info -->
      <div v-if="!state.override_pricing" class="p-3 bg-blue-50 dark:bg-blue-950/20 text-sm">
        <div class="flex items-start gap-2">
          <UIcon name="i-heroicons-information-circle" class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" />
          <div class="text-blue-700 dark:text-blue-300">
            <p class="font-medium">Використовуються налаштування продукту:</p>
            <div v-if="product.discount_price || product.discount_percent" class="mt-1">
              Знижка:
              <span v-if="product.discount_price">{{ product.discount_price }} грн</span>
              <span v-else-if="product.discount_percent">{{ product.discount_percent }}%</span>
            </div>
            <div v-if="product.is_clearance" class="mt-1">
              Уцінка: {{ product.clearance_price }} грн
              <span v-if="product.clearance_reason">({{ product.clearance_reason }})</span>
            </div>
            <div v-if="!product.discount_price && !product.discount_percent && !product.is_clearance" class="mt-1 text-blue-600">
              Знижки та уцінки не налаштовані
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Images -->
    <div class="space-y-3">
      <div class="flex items-center justify-between">
        <h4 class="font-medium">Зображення</h4>
        <UButton
          type="button"
          size="sm"
          variant="outline"
          icon="i-heroicons-photo"
          @click="openImageFilePicker"
        >
          Додати зображення
        </UButton>
      </div>

      <div v-if="state.images.length > 0" class="flex flex-wrap gap-2">
        <div
          v-for="(image, index) in state.images"
          :key="index"
          class="relative group"
        >
          <VSecureImage
            :file-id="image.file_id"
            alt="Variant image"
            width="w-20"
            height="h-20"
            object-fit="cover"
            class="rounded border"
            :class="{ 'ring-2 ring-primary-500': image.is_primary }"
          />
          <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded flex items-center justify-center gap-1">
            <UButton
              type="button"
              size="2xs"
              variant="ghost"
              color="white"
              icon="i-heroicons-star"
              @click="setPrimaryImage(index)"
              :class="{ 'text-yellow-400': image.is_primary }"
            />
            <UButton
              type="button"
              size="2xs"
              variant="ghost"
              color="error"
              icon="i-heroicons-trash"
              @click="removeImage(index)"
            />
          </div>
        </div>
      </div>
      <p v-else class="text-sm text-gray-500">Немає зображень</p>
    </div>

    <USeparator />

    <div class="flex justify-end gap-2">
      <UButton
        type="button"
        variant="outline"
        color="neutral"
        @click="emits('close')"
      >
        Скасувати
      </UButton>
      <UButton
        :loading="loading"
        @click="onSubmit"
      >
        {{ variant ? 'Зберегти' : 'Додати' }}
      </UButton>
    </div>

    <!-- File Picker Modal -->
    <VFilePickerModal
      v-model:is-open="isImageFilePickerOpen"
      :max-files="10"
      file-type="image"
      @select="handleImageFilesSelect"
    />
  </div>
</template>

<script setup lang="ts">
import { CalendarDate, type DateValue } from "@internationalized/date";
import { Calendar, ChevronDown, Percent, Tag, Settings } from "lucide-vue-next";
import type { Product, ProductVariant, ProductVariantStatus, ProductVariantImageFormData } from "~/models/product";
import type { Attribute } from "~/models/attribute";
import VFilePickerModal from "~/components/files/modals/VFilePickerModal.vue";
import type { IFile } from "~/models/files";
import VSecureImage from "~/components/common/VSecureImage.vue";

interface Props {
  product: Product;
  variant: ProductVariant | null;
}

interface Emits {
  (e: "close"): void;
  (e: "success"): void;
}

const props = defineProps<Props>();
const emits = defineEmits<Emits>();
const toast = useToast();
const productStore = useProductStore();

// Parse datetime string to CalendarDate and time parts
const parseDateTime = (dateString: string | null | undefined): { date: CalendarDate | null; hours: string; minutes: string } => {
  if (!dateString) return { date: null, hours: "00", minutes: "00" };
  try {
    const date = new Date(dateString);
    return {
      date: new CalendarDate(date.getFullYear(), date.getMonth() + 1, date.getDate()),
      hours: date.getHours().toString().padStart(2, "0"),
      minutes: date.getMinutes().toString().padStart(2, "0"),
    };
  } catch {
    return { date: null, hours: "00", minutes: "00" };
  }
};

// Format CalendarDate and time to ISO string for API
const formatDateTimeForApi = (calendarDate: DateValue | null, hours: string, minutes: string): string | null => {
  if (!calendarDate) return null;
  const date = new Date(
    calendarDate.year,
    calendarDate.month - 1,
    calendarDate.day,
    parseInt(hours) || 0,
    parseInt(minutes) || 0
  );
  return date.toISOString();
};

// Format date for display
const formatDateForDisplay = (date: DateValue | null, hours: string, minutes: string): string => {
  if (!date) return "Оберіть дату";
  const d = new Date(date.year, date.month - 1, date.day);
  return `${d.toLocaleDateString("uk-UA")} ${hours}:${minutes}`;
};

const parsedDiscountStart = parseDateTime(props.variant?.discount_starts_at);
const parsedDiscountEnd = parseDateTime(props.variant?.discount_ends_at);

// Check if variant has discount
const hasDiscount = ref(
  !!(props.variant?.discount_price || props.variant?.discount_percent)
);

const state = reactive({
  sku: props.variant?.sku || "",
  slug: props.variant?.slug || "",
  name: props.variant?.name || "",
  price: Number(props.variant?.price) || Number(props.product.base_price) || 0,
  stock: Number(props.variant?.stock) || 0,
  status: (props.variant?.status || "draft") as ProductVariantStatus,
  is_default: props.variant?.is_default || false,
  override_pricing: props.variant?.override_pricing || false,
  discount_price: props.variant?.discount_price ? Number(props.variant.discount_price) : null,
  discount_percent: props.variant?.discount_percent ? Number(props.variant.discount_percent) : null,
  discount_starts_date: parsedDiscountStart.date as DateValue | null,
  discount_starts_hours: parsedDiscountStart.hours,
  discount_starts_minutes: parsedDiscountStart.minutes,
  discount_ends_date: parsedDiscountEnd.date as DateValue | null,
  discount_ends_hours: parsedDiscountEnd.hours,
  discount_ends_minutes: parsedDiscountEnd.minutes,
  is_clearance: props.variant?.is_clearance || false,
  clearance_price: props.variant?.clearance_price ? Number(props.variant.clearance_price) : null,
  clearance_reason: props.variant?.clearance_reason || null,
  images: (props.variant?.images || []).map((img, i) => ({
    file_id: img.file_id,
    sort_order: img.sort_order ?? i,
    is_primary: img.is_primary ?? false,
  })) as ProductVariantImageFormData[],
});

// Date picker states
const isDiscountStartPickerOpen = ref(false);
const isDiscountEndPickerOpen = ref(false);

// Hours and minutes options
const hoursOptions = Array.from({ length: 24 }, (_, i) => ({
  label: i.toString().padStart(2, "0"),
  value: i.toString().padStart(2, "0"),
}));

const minutesOptions = Array.from({ length: 60 }, (_, i) => ({
  label: i.toString().padStart(2, "0"),
  value: i.toString().padStart(2, "0"),
}));

// Discount calculations
const calculateClearancePercent = computed(() => {
  if (!state.clearance_price || !state.price) return 0;
  return Math.round((1 - state.clearance_price / state.price) * 100);
});

// Flag to prevent infinite loops during recalculation
let isRecalculating = false;

// Recalculate discount percent when discount price changes
const onDiscountPriceChange = () => {
  if (isRecalculating || !state.price) return;
  isRecalculating = true;

  nextTick(() => {
    if (state.discount_price && state.discount_price > 0) {
      const percent = ((state.price - state.discount_price) / state.price) * 100;
      state.discount_percent = Math.round(percent * 100) / 100;
    } else {
      state.discount_percent = null;
    }
    isRecalculating = false;
  });
};

// Recalculate discount price when discount percent changes
const onDiscountPercentChange = () => {
  if (isRecalculating || !state.price) return;
  isRecalculating = true;

  nextTick(() => {
    if (state.discount_percent && state.discount_percent > 0) {
      const price = state.price * (1 - state.discount_percent / 100);
      state.discount_price = Math.round(price * 100) / 100;
    } else {
      state.discount_price = null;
    }
    isRecalculating = false;
  });
};

// Initialize selected attribute values
const selectedAttributeValues = reactive<Record<number, number | number[]>>({});

// Initialize from existing variant
if (props.variant) {
  for (const attrValue of props.variant.attribute_values) {
    const attr = props.product.attributes.find(a => a.id === attrValue.attribute_id);
    if (attr) {
      if (attr.type === 'multi_select') {
        if (!selectedAttributeValues[attr.id]) {
          selectedAttributeValues[attr.id] = [];
        }
        (selectedAttributeValues[attr.id] as number[]).push(attrValue.id);
      } else {
        selectedAttributeValues[attr.id] = attrValue.id;
      }
    }
  }
}

const statusItems = [
  { label: "Чернетка", value: "draft" },
  { label: "Опубліковано", value: "published" },
];

const getAttributeValueOptions = (attr: Attribute) => {
  return attr.values.map(v => ({
    label: v.value,
    value: v.id,
  }));
};

// Image picker
const isImageFilePickerOpen = ref(false);

const openImageFilePicker = () => {
  isImageFilePickerOpen.value = true;
};

const handleImageFilesSelect = (files: IFile[]) => {
  for (const file of files) {
    if (!state.images.find(img => img.file_id === file.id)) {
      state.images.push({
        file_id: file.id,
        sort_order: state.images.length,
        is_primary: state.images.length === 0,
      });
    }
  }
  isImageFilePickerOpen.value = false;
};

const setPrimaryImage = (index: number) => {
  state.images.forEach((img, i) => {
    img.is_primary = i === index;
  });
};

const removeImage = (index: number) => {
  const wasPrimary = state.images[index].is_primary;
  state.images.splice(index, 1);
  if (wasPrimary && state.images.length > 0) {
    state.images[0].is_primary = true;
  }
};

// Form submission
const loading = ref(false);

const onSubmit = async () => {
  if (!state.sku || !state.slug || state.price === null) {
    toast.add({
      title: "Помилка",
      description: "Заповніть обов'язкові поля",
      color: "error",
    });
    return;
  }

  loading.value = true;

  try {
    // Collect attribute value IDs
    const attributeValueIds: number[] = [];
    for (const attrId in selectedAttributeValues) {
      const value = selectedAttributeValues[attrId];
      if (Array.isArray(value)) {
        attributeValueIds.push(...value);
      } else if (value) {
        attributeValueIds.push(value);
      }
    }

    const payload = {
      sku: state.sku,
      slug: state.slug,
      name: state.name || null,
      price: state.price,
      stock: state.stock,
      status: state.status,
      is_default: state.is_default,
      override_pricing: state.override_pricing,
      discount_price: state.override_pricing && hasDiscount.value ? (state.discount_price || null) : null,
      discount_percent: state.override_pricing && hasDiscount.value ? (state.discount_percent || null) : null,
      discount_starts_at: state.override_pricing && hasDiscount.value ? formatDateTimeForApi(state.discount_starts_date, state.discount_starts_hours, state.discount_starts_minutes) : null,
      discount_ends_at: state.override_pricing && hasDiscount.value ? formatDateTimeForApi(state.discount_ends_date, state.discount_ends_hours, state.discount_ends_minutes) : null,
      is_clearance: state.override_pricing ? state.is_clearance : false,
      clearance_price: state.override_pricing && state.is_clearance ? state.clearance_price : null,
      clearance_reason: state.override_pricing && state.is_clearance ? state.clearance_reason : null,
      attribute_value_ids: attributeValueIds,
      images: state.images,
    };

    let result;
    if (props.variant) {
      result = await productStore.onUpdateVariant(props.product.id, props.variant.id, payload);
    } else {
      result = await productStore.onAddVariant(props.product.id, payload);
    }

    if (result.error.value) {
      toast.add({
        title: "Помилка",
        description: "Не вдалося зберегти варіацію",
        color: "error",
      });
      return;
    }

    toast.add({
      title: "Успіх",
      description: props.variant ? "Варіацію оновлено" : "Варіацію додано",
      color: "success",
    });

    emits("success");
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося зберегти варіацію",
      color: "error",
    });
  } finally {
    loading.value = false;
  }
};
</script>
