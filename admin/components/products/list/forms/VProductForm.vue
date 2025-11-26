<template>
  <UForm :schema="schema" :state="state" class="space-y-6 pb-8" @submit="onSubmit">
    <!-- Basic Info -->
    <div class="space-y-4">
      <h3 class="font-medium text-lg pb-2 border-b border-gray-200 dark:border-gray-700">Основна інформація</h3>

      <UFormField label="Назва" name="name">
        <UInput
          v-model="state.name"
          placeholder="iPhone 15 Pro"
          class="w-full"
          @blur="handleNameBlur"
        />
      </UFormField>

      <UFormField label="Slug" name="slug">
        <div class="flex gap-2">
          <UInput
            v-model="state.slug"
            placeholder="iphone-15-pro"
            class="flex-1"
          />
          <UButton
            type="button"
            variant="outline"
            icon="i-heroicons-sparkles"
            @click="generateSlugFromName"
          >
            Згенерувати
          </UButton>
        </div>
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

        <UFormField label="Статус" name="status">
          <USelect
            v-model="state.status"
            :items="statusItems"
            class="w-full"
          />
        </UFormField>
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
import { Ban, Send } from "lucide-vue-next";
import type { Product, ProductStatus } from "~/models/product";
import type { Attribute } from "~/models/attribute";
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
  short_description: z.string().nullable().optional(),
  description: z.string().nullable().optional(),
  main_image_file_id: z.number().nullable().optional(),
  attribute_ids: z.array(z.number()).optional(),
});

const state = reactive({
  name: props.product?.name || "",
  slug: props.product?.slug || "",
  status: (props.product?.status || "draft") as ProductStatus,
  category_id: props.product?.category_id || null,
  brand_id: props.product?.brand_id || null,
  base_price: Number(props.product?.base_price) || 0,
  short_description: props.product?.short_description || null,
  description: props.product?.description || null,
  main_image_file_id: props.product?.main_image_file_id || null,
  attribute_ids: props.product?.attributes?.map(a => a.id) || [] as number[],
});

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
  return (categoriesData.value || []).map(cat => ({
    label: cat.name,
    value: cat.id,
  }));
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

// Slug generation
const handleNameBlur = () => {
  if (!state.slug && state.name) {
    generateSlugFromName();
  }
};

const generateSlugFromName = async () => {
  if (!state.name) return;

  try {
    const { data, error } = await productStore.onGenerateSlug(state.name);
    if (!error.value && data.value) {
      state.slug = data.value.slug;
    }
  } catch (err) {
    console.error("Failed to generate slug:", err);
  }
};

// Form submission
const loading = ref(false);

const onSubmit = async () => {
  loading.value = true;

  try {
    let result;

    if (props.product) {
      result = await productStore.onUpdateProduct(props.product.id, {
        name: state.name,
        slug: state.slug,
        status: state.status,
        category_id: state.category_id,
        brand_id: state.brand_id,
        base_price: state.base_price,
        short_description: state.short_description,
        description: state.description,
        main_image_file_id: state.main_image_file_id,
      });

      // Sync attributes if changed
      if (!result.error.value) {
        await productStore.onSyncAttributes(props.product.id, state.attribute_ids);
      }
    } else {
      result = await productStore.onCreateProduct({
        name: state.name,
        slug: state.slug,
        status: state.status,
        category_id: state.category_id,
        brand_id: state.brand_id,
        base_price: state.base_price,
        short_description: state.short_description,
        description: state.description,
        main_image_file_id: state.main_image_file_id,
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
