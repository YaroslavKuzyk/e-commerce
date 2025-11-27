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

const state = reactive({
  sku: props.variant?.sku || "",
  slug: props.variant?.slug || "",
  name: props.variant?.name || "",
  price: Number(props.variant?.price) || Number(props.product.base_price) || 0,
  stock: Number(props.variant?.stock) || 0,
  status: (props.variant?.status || "draft") as ProductVariantStatus,
  is_default: props.variant?.is_default || false,
  images: (props.variant?.images || []).map((img, i) => ({
    file_id: img.file_id,
    sort_order: img.sort_order ?? i,
    is_primary: img.is_primary ?? false,
  })) as ProductVariantImageFormData[],
});

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
