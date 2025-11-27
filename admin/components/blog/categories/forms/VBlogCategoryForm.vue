<template>
  <div class="max-w-2xl">
    <UForm :schema="schema" :state="formState" class="space-y-6" @submit="handleSubmit">
      <UFormField label="Назва" name="name" required>
        <UInput
          v-model="formState.name"
          placeholder="Введіть назву категорії"
          class="w-full"
          @blur="generateSlugIfEmpty"
        />
      </UFormField>

      <UFormField label="Опис" name="description" required>
        <VWysiwygEditor v-model="formState.description" />
      </UFormField>

      <UFormField label="Slug" name="slug" required>
        <div class="flex gap-2">
          <UInput
            v-model="formState.slug"
            placeholder="slug-kategoriyi"
            class="flex-1"
          />
          <UButton
            type="button"
            variant="outline"
            :loading="slugLoading"
            @click="generateSlug"
          >
            Згенерувати
          </UButton>
        </div>
      </UFormField>

      <UFormField label="Статус" name="status" required>
        <USelectMenu
          v-model="formState.status"
          :items="statusOptions"
          value-key="value"
          label-key="label"
          class="w-full"
        />
      </UFormField>

      <UFormField label="Порядок сортування" name="sort_order" required>
        <UInput
          v-model.number="formState.sort_order"
          type="number"
          placeholder="0"
          class="w-full"
          :min="0"
        />
      </UFormField>

      <div class="flex justify-end gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
        <UButton
          type="button"
          variant="outline"
          color="neutral"
          @click="$emit('cancel')"
        >
          Скасувати
        </UButton>
        <UButton
          type="submit"
          :loading="submitLoading"
        >
          {{ category ? 'Зберегти' : 'Створити' }}
        </UButton>
      </div>
    </UForm>
  </div>
</template>

<script setup lang="ts">
import { z } from "zod";
import VWysiwygEditor from "~/components/common/VWysiwygEditor.vue";
import type { BlogCategory, BlogCategoryStatus } from "~/models/blogCategory";

interface Props {
  category?: BlogCategory;
}

interface Emits {
  (e: "cancel"): void;
  (e: "success"): void;
}

const props = defineProps<Props>();
const emits = defineEmits<Emits>();
const toast = useToast();
const blogCategoryStore = useBlogCategoryStore();

const schema = z.object({
  name: z.string().min(1, "Назва є обов'язковою"),
  description: z.string().min(1, "Опис є обов'язковим"),
  slug: z.string().min(1, "Slug є обов'язковим"),
  status: z.enum(["draft", "published"]),
  sort_order: z.number().min(0, "Порядок сортування має бути >= 0"),
});

const statusOptions = [
  { label: "Чернетка", value: "draft" },
  { label: "Опубліковано", value: "published" },
];

const formState = reactive({
  name: props.category?.name || "",
  description: props.category?.description || "",
  slug: props.category?.slug || "",
  status: (props.category?.status || "draft") as BlogCategoryStatus,
  sort_order: props.category?.sort_order ?? 0,
});

const submitLoading = ref(false);
const slugLoading = ref(false);

const generateSlugIfEmpty = async () => {
  if (!formState.slug && formState.name) {
    await generateSlug();
  }
};

const generateSlug = async () => {
  if (!formState.name) return;

  slugLoading.value = true;
  try {
    const { data, error } = await blogCategoryStore.onGenerateSlug(formState.name);
    if (!error.value && data.value?.slug) {
      formState.slug = data.value.slug;
    }
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося згенерувати slug",
      color: "error",
    });
  } finally {
    slugLoading.value = false;
  }
};

const handleSubmit = async () => {
  submitLoading.value = true;

  try {
    if (props.category) {
      const { error } = await blogCategoryStore.onUpdateBlogCategory(props.category.id, formState);

      if (error.value) {
        toast.add({
          title: "Помилка",
          description: "Не вдалося оновити категорію",
          color: "error",
        });
        return;
      }

      toast.add({
        title: "Успішно",
        description: "Категорію оновлено",
        color: "success",
      });
    } else {
      const { error } = await blogCategoryStore.onCreateBlogCategory(formState);

      if (error.value) {
        toast.add({
          title: "Помилка",
          description: "Не вдалося створити категорію",
          color: "error",
        });
        return;
      }

      toast.add({
        title: "Успішно",
        description: "Категорію створено",
        color: "success",
      });
    }

    emits("success");
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося зберегти категорію",
      color: "error",
    });
  } finally {
    submitLoading.value = false;
  }
};

// Watch for prop changes (when editing)
watch(
  () => props.category,
  (newCategory) => {
    if (newCategory) {
      formState.name = newCategory.name;
      formState.description = newCategory.description;
      formState.slug = newCategory.slug;
      formState.status = newCategory.status;
      formState.sort_order = newCategory.sort_order;
    }
  },
  { deep: true }
);
</script>
