<template>
  <UForm :schema="schema" :state="state" class="space-y-4" @submit="onSubmit">
    <UFormField label="Назва" name="name" required>
      <UInput
        v-model="state.name"
        placeholder="Apple"
        class="w-full"
      />
    </UFormField>

    <UFormField label="Slug" name="slug" required>
      <UInput
        v-model="state.slug"
        placeholder="apple"
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

    <UFormField label="Опис" name="body_description">
      <VWysiwygEditor v-model="state.body_description" />
    </UFormField>

    <UFormField label="Логотип" name="logo_file_id">
      <div class="space-y-2">
        <div v-if="state.logo_file_id" class="flex items-center gap-2">
          <VSecureImage
            :file-id="state.logo_file_id"
            alt="Logo"
            width="w-20"
            height="h-20"
            object-fit="cover"
            class="rounded border"
          />
          <UButton
            type="button"
            size="sm"
            variant="ghost"
            color="error"
            icon="i-heroicons-trash"
            @click="state.logo_file_id = null"
          />
        </div>
        <UButton
          type="button"
          variant="outline"
          icon="i-heroicons-photo"
          @click="openLogoFilePicker"
        >
          {{ state.logo_file_id ? 'Змінити логотип' : 'Вибрати логотип' }}
        </UButton>
      </div>
    </UFormField>

    <UFormField label="Меню зображення" name="menu_image_file_id">
      <div class="space-y-2">
        <div v-if="state.menu_image_file_id" class="flex items-center gap-2">
          <VSecureImage
            :file-id="state.menu_image_file_id"
            alt="Menu image"
            width="w-20"
            height="h-20"
            object-fit="cover"
            class="rounded border"
          />
          <UButton
            type="button"
            size="sm"
            variant="ghost"
            color="error"
            icon="i-heroicons-trash"
            @click="state.menu_image_file_id = null"
          />
        </div>
        <UButton
          type="button"
          variant="outline"
          icon="i-heroicons-photo"
          @click="openMenuImageFilePicker"
        >
          {{ state.menu_image_file_id ? 'Змінити зображення' : 'Вибрати зображення' }}
        </UButton>
      </div>
    </UFormField>

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

  <!-- File Picker Modals -->
  <VFilePickerModal
    v-model:is-open="isLogoFilePickerOpen"
    :max-files="1"
    file-type="image"
    @select="handleLogoFileSelect"
  />

  <VFilePickerModal
    v-model:is-open="isMenuImageFilePickerOpen"
    :max-files="1"
    file-type="image"
    @select="handleMenuImageFileSelect"
  />
</template>

<script setup lang="ts">
import { z } from "zod";
import { Ban, Send } from "lucide-vue-next";
import type { ProductBrand } from "~/models/productBrand";
import VFilePickerModal from "~/components/files/modals/VFilePickerModal.vue";
import VWysiwygEditor from "~/components/common/VWysiwygEditor.vue";
import type { IFile } from "~/models/files";

interface Props {
  brand: ProductBrand | null;
}

interface Emits {
  (e: "cancel"): void;
  (e: "success"): void;
}

const props = defineProps<Props>();
const emits = defineEmits<Emits>();
const toast = useToast();
const productBrandStore = useProductBrandStore();

const schema = z.object({
  name: z.string().min(1, "Назва є обов'язковою"),
  slug: z.string().min(1, "Slug є обов'язковим"),
  status: z.enum(["draft", "published"]),
  body_description: z.string().optional().nullable(),
  logo_file_id: z.number().nullable().optional(),
  menu_image_file_id: z.number().nullable().optional(),
});

const state = reactive({
  name: props.brand?.name || "",
  slug: props.brand?.slug || "",
  status: (props.brand?.status || "draft") as "draft" | "published",
  body_description: props.brand?.body_description || null,
  logo_file_id: props.brand?.logo_file_id || null,
  menu_image_file_id: props.brand?.menu_image_file_id || null,
});

const statusItems = [
  { label: "Чернетка", value: "draft" },
  { label: "Опубліковано", value: "published" },
];

// File picker state
const isLogoFilePickerOpen = ref(false);
const isMenuImageFilePickerOpen = ref(false);

const openLogoFilePicker = () => {
  isLogoFilePickerOpen.value = true;
};

const openMenuImageFilePicker = () => {
  isMenuImageFilePickerOpen.value = true;
};

const handleLogoFileSelect = (files: IFile[]) => {
  if (files.length > 0 && files[0]) {
    state.logo_file_id = files[0].id;
  }
  isLogoFilePickerOpen.value = false;
};

const handleMenuImageFileSelect = (files: IFile[]) => {
  if (files.length > 0 && files[0]) {
    state.menu_image_file_id = files[0].id;
  }
  isMenuImageFilePickerOpen.value = false;
};

// Form submission
const loading = ref(false);

const onSubmit = async () => {
  loading.value = true;

  try {
    let result;
    if (props.brand) {
      result = await productBrandStore.onUpdateProductBrand(
        props.brand.id,
        state
      );
    } else {
      result = await productBrandStore.onCreateProductBrand(state);
    }

    if (result.error.value) {
      toast.add({
        title: "Помилка",
        description: "Не вдалося зберегти бренд",
        color: "error",
      });
      return;
    }

    toast.add({
      title: "Успіх",
      description: props.brand
        ? "Бренд успішно оновлено"
        : "Бренд успішно створено",
      color: "success",
    });

    // Invalidate product-brands cache
    await refreshNuxtData('product-brands');

    emits("success");
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося зберегти бренд",
      color: "error",
    });
  } finally {
    loading.value = false;
  }
};
</script>
