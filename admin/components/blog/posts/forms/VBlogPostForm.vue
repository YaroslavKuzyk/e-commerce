<template>
  <div>
    <UForm :schema="schema" :state="formState" class="space-y-6" @submit="handleSubmit">
      <UFormField label="Заголовок" name="title" required>
        <UInput
          v-model="formState.title"
          placeholder="Введіть заголовок статті"
          class="w-full"
        />
      </UFormField>

      <UFormField label="Slug" name="slug" required>
        <UInput
          v-model="formState.slug"
          placeholder="slug-statti"
          class="w-full"
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
        <div class="relative">
          <UButton
            type="button"
            variant="outline"
            color="neutral"
            class="w-full justify-start"
            @click="isDatePickerOpen = !isDatePickerOpen"
          >
            <template #leading>
              <Calendar class="w-4 h-4" />
            </template>
            {{ formatDateForDisplay(formState.publication_date, formState.publication_hours, formState.publication_minutes) }}
          </UButton>
          <div
            v-if="isDatePickerOpen"
            class="absolute left-0 top-full mt-1 z-50 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-3"
          >
            <UCalendar v-model="formState.publication_date" />
            <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
              <span class="text-sm text-gray-500">Час:</span>
              <USelectMenu
                v-model="formState.publication_hours"
                :items="hoursOptions"
                value-key="value"
                label-key="label"
                class="w-20"
              />
              <span>:</span>
              <USelectMenu
                v-model="formState.publication_minutes"
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
                @click="formState.publication_date = null; isDatePickerOpen = false"
              >
                Очистити
              </UButton>
              <UButton
                type="button"
                size="sm"
                @click="isDatePickerOpen = false"
              >
                Готово
              </UButton>
            </div>
          </div>
        </div>
      </UFormField>

      <UFormField label="Зображення для прев'ю" name="preview_image_id">
        <div class="space-y-2">
          <div v-if="formState.preview_image_id" class="flex items-center gap-2">
            <VSecureImage
              :file-id="formState.preview_image_id"
              alt="Preview"
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
              @click="formState.preview_image_id = null"
            />
          </div>
          <UButton
            type="button"
            variant="outline"
            icon="i-heroicons-photo"
            @click="isFilePickerOpen = true"
          >
            {{ formState.preview_image_id ? 'Змінити зображення' : 'Вибрати зображення' }}
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

      <USeparator />

      <div class="flex justify-end gap-2">
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
import { CalendarDate, type DateValue } from "@internationalized/date";
import { Calendar } from "lucide-vue-next";
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

// Parse datetime string to CalendarDate and time parts
const parsePublicationDate = (dateString: string | null | undefined): { date: CalendarDate | null; hours: string; minutes: string } => {
  if (!dateString) return { date: null, hours: "12", minutes: "00" };
  try {
    const date = new Date(dateString);
    return {
      date: new CalendarDate(date.getFullYear(), date.getMonth() + 1, date.getDate()),
      hours: date.getHours().toString().padStart(2, "0"),
      minutes: date.getMinutes().toString().padStart(2, "0"),
    };
  } catch {
    return { date: null, hours: "12", minutes: "00" };
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

const parsedDate = parsePublicationDate(props.post?.publication_date);

const formState = reactive({
  title: props.post?.title || "",
  short_description: props.post?.short_description || "",
  slug: props.post?.slug || "",
  content: props.post?.content || "",
  preview_image_id: props.post?.preview_image_id || null as number | null,
  status: (props.post?.status || "draft") as BlogPostStatus,
  publication_date: parsedDate.date as DateValue | null,
  publication_hours: parsedDate.hours,
  publication_minutes: parsedDate.minutes,
  blog_category_id: props.post?.blog_category_id || null as number | null,
  product_ids: props.post?.products?.map(p => p.id) || [] as number[],
});

const isDatePickerOpen = ref(false);

// Hours and minutes options
const hoursOptions = Array.from({ length: 24 }, (_, i) => ({
  label: i.toString().padStart(2, "0"),
  value: i.toString().padStart(2, "0"),
}));

const minutesOptions = Array.from({ length: 60 }, (_, i) => ({
  label: i.toString().padStart(2, "0"),
  value: i.toString().padStart(2, "0"),
}));

const submitLoading = ref(false);
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
      publication_date: formatDateTimeForApi(formState.publication_date, formState.publication_hours, formState.publication_minutes),
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
      const parsed = parsePublicationDate(newPost.publication_date);
      formState.publication_date = parsed.date;
      formState.publication_hours = parsed.hours;
      formState.publication_minutes = parsed.minutes;
      formState.blog_category_id = newPost.blog_category_id;
      formState.product_ids = newPost.products?.map(p => p.id) || [];
    }
  },
  { deep: true }
);
</script>
