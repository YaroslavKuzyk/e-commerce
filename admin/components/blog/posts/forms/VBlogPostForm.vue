<template>
  <div class="max-w-4xl">
    <UForm :schema="schema" :state="formState" class="space-y-6" @submit="handleSubmit">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main content column -->
        <div class="lg:col-span-2 space-y-6">
          <UFormField label="Заголовок" name="title" required>
            <UInput
              v-model="formState.title"
              placeholder="Введіть заголовок статті"
              class="w-full"
              @blur="generateSlugIfEmpty"
            />
          </UFormField>

          <UFormField label="Короткий опис" name="short_description" required>
            <UTextarea
              v-model="formState.short_description"
              placeholder="Введіть короткий опис статті"
              class="w-full"
              :rows="3"
            />
          </UFormField>

          <UFormField label="Контент" name="content" required>
            <VWysiwygEditor v-model="formState.content" />
          </UFormField>
        </div>

        <!-- Sidebar column -->
        <div class="space-y-6">
          <UFormField label="Slug" name="slug" required>
            <div class="flex gap-2">
              <UInput
                v-model="formState.slug"
                placeholder="slug-statti"
                class="flex-1"
              />
              <UButton
                type="button"
                variant="outline"
                size="sm"
                :loading="slugLoading"
                @click="generateSlug"
              >
                <UIcon name="i-heroicons-arrow-path" />
              </UButton>
            </div>
          </UFormField>

          <UFormField label="Категорія" name="blog_category_id" required>
            <USelectMenu
              v-model="formState.blog_category_id"
              :items="categoryOptions"
              value-key="value"
              label-key="label"
              placeholder="Оберіть категорію"
              class="w-full"
            />
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

          <UFormField label="Дата публікації" name="publication_date">
            <UInput
              v-model="formState.publication_date"
              type="datetime-local"
              class="w-full"
            />
          </UFormField>

          <UFormField label="Зображення для прев'ю" name="preview_image_id">
            <div class="space-y-2">
              <div
                v-if="formState.preview_image_id"
                class="relative w-full h-40 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800"
              >
                <VSecureImage
                  :file-id="formState.preview_image_id"
                  alt="Preview"
                  width="w-full"
                  height="h-40"
                  object-fit="cover"
                />
                <UButton
                  size="xs"
                  color="error"
                  variant="solid"
                  icon="i-heroicons-x-mark"
                  class="absolute top-2 right-2"
                  @click="formState.preview_image_id = null"
                />
              </div>
              <UButton
                type="button"
                variant="outline"
                class="w-full"
                @click="isFilePickerOpen = true"
              >
                <template #leading>
                  <UIcon name="i-heroicons-photo" />
                </template>
                {{ formState.preview_image_id ? 'Змінити зображення' : 'Обрати зображення' }}
              </UButton>
            </div>
          </UFormField>

          <UFormField label="Пов'язані продукти" name="product_ids">
            <USelectMenu
              v-model="formState.product_ids"
              :items="productOptions"
              value-key="value"
              label-key="label"
              multiple
              placeholder="Оберіть продукти"
              class="w-full"
            />
          </UFormField>
        </div>
      </div>

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
          {{ post ? 'Зберегти' : 'Створити' }}
        </UButton>
      </div>
    </UForm>

    <!-- File Picker Modal -->
    <VFilePickerModal
      v-model:is-open="isFilePickerOpen"
      file-type="image"
      @select="handleFileSelect"
    />
  </div>
</template>

<script setup lang="ts">
import { z } from "zod";
import VWysiwygEditor from "~/components/common/VWysiwygEditor.vue";
import VFilePickerModal from "~/components/files/modals/VFilePickerModal.vue";
import type { BlogPost, BlogPostStatus } from "~/models/blogPost";
import type { IFile } from "~/models/files";

interface Props {
  post?: BlogPost;
}

interface Emits {
  (e: "cancel"): void;
  (e: "success"): void;
}

const props = defineProps<Props>();
const emits = defineEmits<Emits>();
const toast = useToast();
const blogPostStore = useBlogPostStore();
const blogCategoryStore = useBlogCategoryStore();
const productStore = useProductStore();

const schema = z.object({
  title: z.string().min(1, "Заголовок є обов'язковим"),
  short_description: z.string().min(1, "Короткий опис є обов'язковим"),
  slug: z.string().min(1, "Slug є обов'язковим"),
  content: z.string().min(1, "Контент є обов'язковим"),
  status: z.enum(["draft", "published"]),
  blog_category_id: z.number().min(1, "Категорія є обов'язковою"),
});

const statusOptions = [
  { label: "Чернетка", value: "draft" },
  { label: "Опубліковано", value: "published" },
];

// Format datetime for input
const formatDateTimeForInput = (dateString: string | null | undefined): string => {
  if (!dateString) return "";
  const date = new Date(dateString);
  return date.toISOString().slice(0, 16);
};

const formState = reactive({
  title: props.post?.title || "",
  short_description: props.post?.short_description || "",
  slug: props.post?.slug || "",
  content: props.post?.content || "",
  preview_image_id: props.post?.preview_image_id || null as number | null,
  status: (props.post?.status || "draft") as BlogPostStatus,
  publication_date: formatDateTimeForInput(props.post?.publication_date),
  blog_category_id: props.post?.blog_category_id || null as number | null,
  product_ids: props.post?.products?.map(p => p.id) || [] as number[],
});

const submitLoading = ref(false);
const slugLoading = ref(false);
const isFilePickerOpen = ref(false);

// Fetch categories for dropdown
const { data: categoriesData } = await blogCategoryStore.fetchBlogCategories();

const categoryOptions = computed(() => {
  return (categoriesData.value?.data || []).map(cat => ({
    label: cat.name,
    value: cat.id,
  }));
});

// Fetch products for dropdown
const { data: productsData } = await productStore.fetchProducts({ per_page: 100 });

const productOptions = computed(() => {
  return (productsData.value?.data || []).map(product => ({
    label: product.name,
    value: product.id,
  }));
});

const generateSlugIfEmpty = async () => {
  if (!formState.slug && formState.title) {
    await generateSlug();
  }
};

const generateSlug = async () => {
  if (!formState.title) return;

  slugLoading.value = true;
  try {
    const { data, error } = await blogPostStore.onGenerateSlug(formState.title);
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

const handleFileSelect = (files: IFile[]) => {
  if (files.length > 0) {
    formState.preview_image_id = files[0].id;
  }
  isFilePickerOpen.value = false;
};

const handleSubmit = async () => {
  submitLoading.value = true;

  try {
    const payload = {
      title: formState.title,
      short_description: formState.short_description,
      slug: formState.slug,
      content: formState.content,
      preview_image_id: formState.preview_image_id,
      status: formState.status,
      publication_date: formState.publication_date || null,
      blog_category_id: formState.blog_category_id!,
      product_ids: formState.product_ids,
    };

    if (props.post) {
      const { error } = await blogPostStore.onUpdateBlogPost(props.post.id, payload);

      if (error.value) {
        toast.add({
          title: "Помилка",
          description: "Не вдалося оновити статтю",
          color: "error",
        });
        return;
      }

      toast.add({
        title: "Успішно",
        description: "Статтю оновлено",
        color: "success",
      });
    } else {
      const { error } = await blogPostStore.onCreateBlogPost(payload);

      if (error.value) {
        toast.add({
          title: "Помилка",
          description: "Не вдалося створити статтю",
          color: "error",
        });
        return;
      }

      toast.add({
        title: "Успішно",
        description: "Статтю створено",
        color: "success",
      });
    }

    emits("success");
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося зберегти статтю",
      color: "error",
    });
  } finally {
    submitLoading.value = false;
  }
};

// Watch for prop changes (when editing)
watch(
  () => props.post,
  (newPost) => {
    if (newPost) {
      formState.title = newPost.title;
      formState.short_description = newPost.short_description;
      formState.slug = newPost.slug;
      formState.content = newPost.content;
      formState.preview_image_id = newPost.preview_image_id;
      formState.status = newPost.status;
      formState.publication_date = formatDateTimeForInput(newPost.publication_date);
      formState.blog_category_id = newPost.blog_category_id;
      formState.product_ids = newPost.products?.map(p => p.id) || [];
    }
  },
  { deep: true }
);
</script>
