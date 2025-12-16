<template>
  <UForm :schema="schema" :state="state" class="space-y-6 pb-8" @submit="onSubmit">
    <!-- Basic Info -->
    <div class="space-y-4">
      <h3 class="font-medium text-lg pb-2 border-b border-gray-200 dark:border-gray-700">Основна інформація</h3>

      <UFormField label="Назва" name="name" required>
        <UInput
          v-model="state.name"
          placeholder="iPhone 15 Pro"
          class="w-full"
        />
      </UFormField>

      <UFormField label="Slug" name="slug" required>
        <UInput
          v-model="state.slug"
          placeholder="iphone-15-pro"
          class="w-full"
        />
      </UFormField>

      <div class="grid grid-cols-2 gap-4">
        <UFormField label="Категорія" name="category_id">
          <USelectMenu
            v-model="state.category_id"
            :items="categoryOptions"
            placeholder="Виберіть категорію"
            value-key="value"
            label-key="label"
            class="w-full"
          />
        </UFormField>

        <UFormField label="Бренд" name="brand_id">
          <USelectMenu
            v-model="state.brand_id"
            :items="brandOptions"
            placeholder="Виберіть бренд"
            value-key="value"
            label-key="label"
            class="w-full"
          />
        </UFormField>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <UFormField label="Базова ціна" name="base_price">
          <UInput
            v-model.number="state.base_price"
            type="number"
            step="0.01"
            placeholder="0.00"
            class="w-full"
          />
        </UFormField>

        <UFormField label="Статус" name="status" required>
          <USelect
            v-model="state.status"
            :items="statusItems"
            class="w-full"
          />
        </UFormField>
      </div>

      <!-- Discount Section (Collapsible) -->
      <div class="rounded-lg border border-amber-200 dark:border-amber-800 overflow-hidden">
        <button
          type="button"
          class="w-full flex items-center justify-between p-4 bg-amber-50 dark:bg-amber-950/20 hover:bg-amber-100 dark:hover:bg-amber-950/30 transition-colors"
          @click="hasDiscount = !hasDiscount"
        >
          <div class="flex items-center gap-3">
            <UCheckbox v-model="hasDiscount" @click.stop />
            <div class="flex items-center gap-2">
              <Percent class="w-5 h-5 text-amber-600" />
              <span class="font-medium text-amber-800 dark:text-amber-200">Знижка</span>
            </div>
          </div>
          <ChevronDown
            class="w-5 h-5 text-amber-600 transition-transform"
            :class="{ 'rotate-180': hasDiscount }"
          />
        </button>

        <div v-if="hasDiscount" class="p-4 space-y-4 bg-amber-50/50 dark:bg-amber-950/10">
          <div class="grid grid-cols-2 gap-4">
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

          <div class="grid grid-cols-2 gap-4">
            <UFormField label="Початок знижки" name="discount_starts_at">
              <div class="relative">
                <UButton
                  type="button"
                  variant="outline"
                  color="neutral"
                  class="w-full justify-start"
                  @click="isDiscountStartPickerOpen = !isDiscountStartPickerOpen"
                >
                  <template #leading>
                    <Calendar class="w-4 h-4" />
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
                      size="sm"
                      variant="ghost"
                      color="neutral"
                      @click="state.discount_starts_date = null; isDiscountStartPickerOpen = false"
                    >
                      Очистити
                    </UButton>
                    <UButton
                      type="button"
                      size="sm"
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
                  class="w-full justify-start"
                  @click="isDiscountEndPickerOpen = !isDiscountEndPickerOpen"
                >
                  <template #leading>
                    <Calendar class="w-4 h-4" />
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
                      size="sm"
                      variant="ghost"
                      color="neutral"
                      @click="state.discount_ends_date = null; isDiscountEndPickerOpen = false"
                    >
                      Очистити
                    </UButton>
                    <UButton
                      type="button"
                      size="sm"
                      @click="isDiscountEndPickerOpen = false"
                    >
                      Готово
                    </UButton>
                  </div>
                </div>
              </div>
            </UFormField>
          </div>

          <div v-if="state.discount_price && state.base_price" class="text-sm text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/30 p-3 rounded-lg">
            Економія: <strong>{{ (state.base_price - state.discount_price).toFixed(2) }} грн</strong>
          </div>
        </div>
      </div>

      <!-- Clearance Section (Collapsible) -->
      <div class="rounded-lg border border-red-200 dark:border-red-800 overflow-hidden">
        <button
          type="button"
          class="w-full flex items-center justify-between p-4 bg-red-50 dark:bg-red-950/20 hover:bg-red-100 dark:hover:bg-red-950/30 transition-colors"
          @click="state.is_clearance = !state.is_clearance"
        >
          <div class="flex items-center gap-3">
            <UCheckbox v-model="state.is_clearance" @click.stop />
            <div class="flex items-center gap-2">
              <Tag class="w-5 h-5 text-red-600" />
              <span class="font-medium text-red-800 dark:text-red-200">Уцінка</span>
            </div>
          </div>
          <ChevronDown
            class="w-5 h-5 text-red-600 transition-transform"
            :class="{ 'rotate-180': state.is_clearance }"
          />
        </button>

        <div v-if="state.is_clearance" class="p-4 space-y-4 bg-red-50/50 dark:bg-red-950/10">
          <UFormField label="Ціна уцінки" name="clearance_price" required>
            <UInput
              v-model.number="state.clearance_price"
              type="number"
              step="0.01"
              placeholder="0.00"
              class="w-full"
            />
          </UFormField>

          <UFormField label="Причина уцінки" name="clearance_reason" required>
            <UTextarea
              v-model="state.clearance_reason"
              placeholder="Вкажіть причину уцінки (пошкодження упаковки, вітринний зразок, тощо)..."
              :rows="2"
              class="w-full"
            />
          </UFormField>

          <div v-if="state.clearance_price" class="text-sm text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/30 p-3 rounded-lg">
            Знижка: <strong>{{ calculateClearancePercent }}%</strong> від базової ціни
          </div>
        </div>
      </div>

      <UFormField label="Короткий опис" name="short_description">
        <UTextarea
          v-model="state.short_description"
          placeholder="Короткий опис продукту..."
          :rows="2"
          class="w-full"
        />
      </UFormField>

      <UFormField label="Повний опис" name="description">
        <VWysiwygEditor v-model="state.description" />
      </UFormField>

      <UFormField label="Головне зображення" name="main_image_file_id">
        <div class="space-y-2">
          <div v-if="state.main_image_file_id" class="flex items-center gap-2">
            <VSecureImage
              :file-id="state.main_image_file_id"
              alt="Main image"
              width="w-24"
              height="h-24"
              object-fit="cover"
              class="rounded border"
            />
            <UButton
              type="button"
              size="sm"
              variant="ghost"
              color="error"
              icon="i-heroicons-trash"
              @click="state.main_image_file_id = null"
            />
          </div>
          <UButton
            type="button"
            variant="outline"
            icon="i-heroicons-photo"
            @click="openMainImageFilePicker"
          >
            {{ state.main_image_file_id ? 'Змінити зображення' : 'Вибрати зображення' }}
          </UButton>
        </div>
      </UFormField>
    </div>

    <!-- Attributes Section -->
    <div class="space-y-4">
      <h3 class="font-medium text-lg pb-2 border-b border-gray-200 dark:border-gray-700">Атрибути продукту</h3>
      <p class="text-sm text-gray-500">
        Виберіть атрибути, які будуть доступні для варіацій цього продукту
      </p>

      <div class="flex flex-wrap gap-2">
        <UBadge
          v-for="attr in selectedAttributes"
          :key="attr.id"
          variant="soft"
          size="lg"
          class="flex items-center gap-1"
        >
          {{ attr.name }}
          <UButton
            type="button"
            size="2xs"
            variant="ghost"
            icon="i-heroicons-x-mark"
            class="ml-1"
            @click="removeAttribute(attr.id)"
          />
        </UBadge>
      </div>

      <UButton
        type="button"
        variant="outline"
        icon="i-heroicons-plus"
        @click="openAttributePicker"
      >
        Додати атрибут
      </UButton>
    </div>

    <USeparator />

    <div class="flex justify-end gap-2">
      <UButton
        type="button"
        variant="outline"
        color="neutral"
        @click="emits('cancel')"
      >
        <template #leading>
          <Ban class="w-4 h-4" />
        </template>
        Скасувати
      </UButton>
      <UButton
        type="submit"
        :loading="loading"
      >
        <template #leading>
          <Send class="w-4 h-4" />
        </template>
        Підтвердити
      </UButton>
    </div>
  </UForm>

  <!-- File Picker Modal -->
  <VFilePickerModal
    v-model:is-open="isMainImageFilePickerOpen"
    :max-files="1"
    file-type="image"
    @select="handleMainImageFileSelect"
  />

  <!-- Attribute Picker Modal -->
  <UModal v-model:open="isAttributePickerOpen" title="Вибрати атрибути">
    <template #body>
      <div class="space-y-4 p-4">
        <div
          v-for="attr in availableAttributes"
          :key="attr.id"
          class="flex items-center gap-2"
        >
          <UCheckbox
            :model-value="state.attribute_ids.includes(attr.id)"
            @update:model-value="toggleAttribute(attr.id, $event)"
          />
          <span>{{ attr.name }}</span>
          <UBadge variant="soft" size="xs">{{ typeLabels[attr.type] }}</UBadge>
        </div>
        <div v-if="availableAttributes.length === 0" class="text-gray-500 text-sm text-center py-4">
          Немає доступних атрибутів
        </div>
      </div>
    </template>
  </UModal>
</template>

<script setup lang="ts">
import { z } from "zod";
import { CalendarDate, type DateValue } from "@internationalized/date";
import { Ban, Send, Calendar, ChevronDown, Percent, Tag } from "lucide-vue-next";
import type { Product, ProductStatus } from "~/models/product";
import type { ProductCategory } from "~/models/productCategory";
import VSecureImage from "~/components/common/VSecureImage.vue";
import VFilePickerModal from "~/components/files/modals/VFilePickerModal.vue";
import VWysiwygEditor from "~/components/common/VWysiwygEditor.vue";
import type { IFile } from "~/models/files";

interface Props {
  product: Product | null;
}

interface Emits {
  (e: "cancel"): void;
  (e: "success"): void;
  (e: "created", productId: number): void;
}

const props = defineProps<Props>();
const emits = defineEmits<Emits>();
const toast = useToast();
const productStore = useProductStore();
const productCategoryStore = useProductCategoryStore();
const productBrandStore = useProductBrandStore();
const attributeStore = useAttributeStore();

const schema = z.object({
  name: z.string().min(1, "Назва є обов'язковою"),
  slug: z.string().min(1, "Slug є обов'язковим"),
  status: z.enum(["draft", "published"]),
  category_id: z.number().nullable().optional(),
  brand_id: z.number().nullable().optional(),
  base_price: z.number().min(0).optional(),
  discount_price: z.number().min(0).nullable().optional(),
  discount_percent: z.number().min(0).max(100).nullable().optional(),
  is_clearance: z.boolean().optional(),
  clearance_price: z.number().min(0).nullable().optional(),
  clearance_reason: z.string().nullable().optional(),
  short_description: z.string().nullable().optional(),
  description: z.string().nullable().optional(),
  main_image_file_id: z.number().nullable().optional(),
  attribute_ids: z.array(z.number()).optional(),
});

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

const parsedDiscountStart = parseDateTime(props.product?.discount_starts_at);
const parsedDiscountEnd = parseDateTime(props.product?.discount_ends_at);

// Check if product has discount
const hasDiscount = ref(
  !!(props.product?.discount_price || props.product?.discount_percent)
);

const state = reactive({
  name: props.product?.name || "",
  slug: props.product?.slug || "",
  status: (props.product?.status || "draft") as ProductStatus,
  category_id: props.product?.category_id || null,
  brand_id: props.product?.brand_id || null,
  base_price: Number(props.product?.base_price) || 0,
  discount_price: props.product?.discount_price ? Number(props.product.discount_price) : null,
  discount_percent: props.product?.discount_percent ? Number(props.product.discount_percent) : null,
  discount_starts_date: parsedDiscountStart.date as DateValue | null,
  discount_starts_hours: parsedDiscountStart.hours,
  discount_starts_minutes: parsedDiscountStart.minutes,
  discount_ends_date: parsedDiscountEnd.date as DateValue | null,
  discount_ends_hours: parsedDiscountEnd.hours,
  discount_ends_minutes: parsedDiscountEnd.minutes,
  is_clearance: props.product?.is_clearance || false,
  clearance_price: props.product?.clearance_price ? Number(props.product.clearance_price) : null,
  clearance_reason: props.product?.clearance_reason || null,
  short_description: props.product?.short_description || null,
  description: props.product?.description || null,
  main_image_file_id: props.product?.main_image_file_id || null,
  attribute_ids: props.product?.attributes?.map(a => a.id) || [] as number[],
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

const statusItems = [
  { label: "Чернетка", value: "draft" },
  { label: "Опубліковано", value: "published" },
];

const typeLabels: Record<string, string> = {
  select: 'Вибір',
  multi_select: 'Множинний',
  checkbox: 'Чекбокс',
  switch: 'Перемикач',
};

// Fetch categories and brands
const { data: categoriesData } = await productCategoryStore.fetchProductCategories();
const { data: brandsData } = await productBrandStore.fetchProductBrands();
const { data: attributesData } = await attributeStore.fetchAttributes();

const categoryOptions = computed(() => {
  const flatten = (cats: ProductCategory[], level = 0): { label: string; value: number }[] => {
    return cats.reduce((acc: { label: string; value: number }[], cat) => {
      const prefix = level > 0 ? '— '.repeat(level) : '';
      acc.push({
        label: prefix + cat.name,
        value: cat.id,
      });
      if (cat.subcategories && cat.subcategories.length > 0) {
        acc.push(...flatten(cat.subcategories, level + 1));
      }
      return acc;
    }, []);
  };
  return flatten(categoriesData.value || []);
});

const brandOptions = computed(() => {
  return (brandsData.value?.data || []).map(brand => ({
    label: brand.name,
    value: brand.id,
  }));
});

const availableAttributes = computed(() => {
  return attributesData.value?.data || [];
});

const selectedAttributes = computed(() => {
  return availableAttributes.value.filter(attr => state.attribute_ids.includes(attr.id));
});

// Discount calculations
const calculateClearancePercent = computed(() => {
  if (!state.clearance_price || !state.base_price) return 0;
  return Math.round((1 - state.clearance_price / state.base_price) * 100);
});

// Flag to prevent infinite loops during recalculation
let isRecalculating = false;

// Recalculate discount percent when discount price changes
const onDiscountPriceChange = () => {
  if (isRecalculating || !state.base_price) return;
  isRecalculating = true;

  nextTick(() => {
    if (state.discount_price && state.discount_price > 0) {
      const percent = ((state.base_price - state.discount_price) / state.base_price) * 100;
      state.discount_percent = Math.round(percent * 100) / 100; // Round to 2 decimal places
    } else {
      state.discount_percent = null;
    }
    isRecalculating = false;
  });
};

// Recalculate discount price when discount percent changes
const onDiscountPercentChange = () => {
  if (isRecalculating || !state.base_price) return;
  isRecalculating = true;

  nextTick(() => {
    if (state.discount_percent && state.discount_percent > 0) {
      const price = state.base_price * (1 - state.discount_percent / 100);
      state.discount_price = Math.round(price * 100) / 100; // Round to 2 decimal places
    } else {
      state.discount_price = null;
    }
    isRecalculating = false;
  });
};

// File picker
const isMainImageFilePickerOpen = ref(false);

const openMainImageFilePicker = () => {
  isMainImageFilePickerOpen.value = true;
};

const handleMainImageFileSelect = (files: IFile[]) => {
  if (files.length > 0 && files[0]) {
    state.main_image_file_id = files[0].id;
  }
  isMainImageFilePickerOpen.value = false;
};

// Attribute picker
const isAttributePickerOpen = ref(false);

const openAttributePicker = () => {
  isAttributePickerOpen.value = true;
};

const toggleAttribute = (attributeId: number, checked: boolean) => {
  if (checked) {
    if (!state.attribute_ids.includes(attributeId)) {
      state.attribute_ids.push(attributeId);
    }
  } else {
    state.attribute_ids = state.attribute_ids.filter(id => id !== attributeId);
  }
};

const removeAttribute = (attributeId: number) => {
  state.attribute_ids = state.attribute_ids.filter(id => id !== attributeId);
};

// Form submission
const loading = ref(false);

const onSubmit = async () => {
  loading.value = true;

  try {
    let result;

    const productData = {
      name: state.name,
      slug: state.slug,
      status: state.status,
      category_id: state.category_id,
      brand_id: state.brand_id,
      base_price: state.base_price,
      discount_price: hasDiscount.value ? (state.discount_price || null) : null,
      discount_percent: hasDiscount.value ? (state.discount_percent || null) : null,
      discount_starts_at: hasDiscount.value ? formatDateTimeForApi(state.discount_starts_date, state.discount_starts_hours, state.discount_starts_minutes) : null,
      discount_ends_at: hasDiscount.value ? formatDateTimeForApi(state.discount_ends_date, state.discount_ends_hours, state.discount_ends_minutes) : null,
      is_clearance: state.is_clearance,
      clearance_price: state.is_clearance ? state.clearance_price : null,
      clearance_reason: state.is_clearance ? state.clearance_reason : null,
      short_description: state.short_description,
      description: state.description,
      main_image_file_id: state.main_image_file_id,
    };

    if (props.product) {
      result = await productStore.onUpdateProduct(props.product.id, productData);

      // Sync attributes if changed
      if (!result.error.value) {
        await productStore.onSyncAttributes(props.product.id, state.attribute_ids);
      }
    } else {
      result = await productStore.onCreateProduct({
        ...productData,
        attribute_ids: state.attribute_ids,
      });
    }

    if (result.error.value) {
      toast.add({
        title: "Помилка",
        description: "Не вдалося зберегти продукт",
        color: "error",
      });
      return;
    }

    toast.add({
      title: "Успіх",
      description: props.product
        ? "Продукт успішно оновлено"
        : "Продукт успішно створено",
      color: "success",
    });

    await refreshNuxtData('products');

    if (props.product) {
      emits("success");
    } else {
      // Emit created event with the new product ID
      const createdProduct = result.data.value;
      if (createdProduct?.id) {
        emits("created", createdProduct.id);
      } else {
        emits("success");
      }
    }
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося зберегти продукт",
      color: "error",
    });
  } finally {
    loading.value = false;
  }
};
</script>
