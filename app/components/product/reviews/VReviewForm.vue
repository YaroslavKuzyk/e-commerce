<template>
  <UModal v-model:open="isOpen" :ui="{ width: 'sm:max-w-xl' }">
    <template #content>
      <div class="flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div
          class="flex items-center justify-between p-4 border-b border-gray-200 shrink-0"
        >
          <h3 class="text-lg font-semibold">Написати відгук</h3>
          <UButton icon="i-lucide-x" variant="ghost" size="sm" @click="close" />
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-4">
          <form @submit.prevent="submitReview" class="space-y-4">
            <!-- Rating -->
            <div>
              <label class="block text-sm font-medium mb-2"
                >Оцініть товар</label
              >
              <div class="flex justify-between">
                <div
                  v-for="i in 5"
                  :key="i"
                  class="flex flex-col items-center flex-1"
                >
                  <button
                    type="button"
                    class="p-1 transition-transform hover:scale-110"
                    @click="form.rating = i"
                    @mouseenter="hoverRating = i"
                    @mouseleave="hoverRating = 0"
                  >
                    <Star
                      class="w-12 h-12 transition-colors"
                      :class="
                        i <= (hoverRating || form.rating)
                          ? 'text-green-500 fill-green-500'
                          : 'text-gray-300'
                      "
                    />
                  </button>
                  <span class="text-xs text-dimmed mt-1">{{
                    ratingLabels[i - 1]
                  }}</span>
                </div>
              </div>
              <p v-if="errors.rating" class="text-sm text-red-500 mt-1">
                {{ errors.rating }}
              </p>
            </div>

            <!-- Advantages -->
            <div>
              <label class="block text-sm font-medium mb-1">Переваги</label>
              <UTextarea
                v-model="form.advantages"
                placeholder="Що вам сподобалось?"
                :rows="2"
                class="w-full"
              />
            </div>

            <!-- Disadvantages -->
            <div>
              <label class="block text-sm font-medium mb-1">Недоліки</label>
              <UTextarea
                v-model="form.disadvantages"
                placeholder="Що вам не сподобалось?"
                :rows="2"
                class="w-full"
              />
            </div>

            <!-- Comment -->
            <div>
              <label class="block text-sm font-medium mb-1">Коментар</label>
              <UTextarea
                v-model="form.comment"
                placeholder="Ваш відгук..."
                :rows="3"
                class="w-full"
              />
            </div>

            <!-- YouTube URLs -->
            <div>
              <div class="flex items-center gap-2 mb-1">
                <Youtube class="w-4 h-4" />
                <label class="text-sm font-medium">Додайте відео</label>
              </div>
              <p class="text-xs text-dimmed mb-2">
                Додавайте до 5 відео з Youtube
              </p>
              <div class="space-y-2">
                <div
                  v-for="(_, index) in form.youtube_urls"
                  :key="index"
                  class="flex gap-2"
                >
                  <UInput
                    v-model="form.youtube_urls[index]"
                    placeholder="https://youtube.com/..."
                    class="flex-1"
                  />
                  <UButton
                    v-if="form.youtube_urls.length > 1"
                    icon="i-lucide-x"
                    variant="ghost"
                    color="error"
                    size="sm"
                    @click="removeYoutubeUrl(index)"
                  />
                </div>
              </div>
              <UButton
                v-if="form.youtube_urls.length < 5"
                variant="ghost"
                size="xs"
                class="mt-2"
                @click="addYoutubeUrl"
              >
                + Додати посилання
              </UButton>
            </div>

            <!-- Images -->
            <div>
              <div class="flex items-center gap-2 mb-1">
                <ImageIcon class="w-4 h-4" />
                <label class="text-sm font-medium">Додайте фото</label>
              </div>
              <p class="text-xs text-dimmed mb-2">
                Додавайте до 10 зображень (.jpg, .gif, .png), до 5 МБ кожне
              </p>

              <div class="flex flex-wrap gap-2">
                <!-- Preview images -->
                <div
                  v-for="(preview, index) in imagePreviews"
                  :key="index"
                  class="relative w-16 h-16"
                >
                  <img
                    :src="preview.url"
                    class="w-full h-full object-cover rounded-lg border"
                  />
                  <button
                    type="button"
                    class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600"
                    @click="removeImage(index)"
                  >
                    &times;
                  </button>
                </div>

                <!-- Upload button -->
                <label
                  v-if="imagePreviews.length < 10"
                  class="w-16 h-16 border-2 border-dashed rounded-lg flex flex-col items-center justify-center cursor-pointer hover:border-primary hover:bg-gray-50 transition-colors"
                >
                  <input
                    type="file"
                    accept="image/jpeg,image/png,image/gif"
                    multiple
                    class="hidden"
                    @change="handleFileSelect"
                  />
                  <Plus class="w-5 h-5 text-gray-400" />
                  <span class="text-xs text-gray-400">Фото</span>
                </label>
              </div>
            </div>

            <!-- Author Info -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium mb-1">
                  Ваше ім'я та прізвище
                </label>
                <UInput
                  v-model="form.author_name"
                  placeholder="Ім'я Прізвище"
                  class="w-full"
                />
                <p v-if="errors.author_name" class="text-sm text-red-500 mt-1">
                  {{ errors.author_name }}
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">
                  Електронна пошта
                </label>
                <UInput
                  v-model="form.author_email"
                  type="email"
                  placeholder="email@example.com"
                  class="w-full"
                />
                <p v-if="errors.author_email" class="text-sm text-red-500 mt-1">
                  {{ errors.author_email }}
                </p>
              </div>
            </div>

            <!-- Notify on Reply -->
            <div class="flex items-center gap-2">
              <UCheckbox v-model="form.notify_on_reply" />
              <span class="text-sm"
                >Повідомляти про відповіді по електронній пошті</span
              >
            </div>

            <!-- Info -->
            <p class="text-xs text-dimmed">
              Щоб ваш відгук пройшов модерацію і був опублікований, ознайомтесь
              з правилами публікації
            </p>
          </form>
        </div>

        <!-- Footer -->
        <div
          class="flex gap-3 justify-end p-4 border-t border-gray-200 shrink-0"
        >
          <UButton variant="outline" @click="close">Скасувати</UButton>
          <UButton @click="submitReview" :loading="isSubmitting">
            Залишити відгук
          </UButton>
        </div>
      </div>
    </template>
  </UModal>
</template>

<script lang="ts" setup>
import { Star, Youtube, Image as ImageIcon, Plus } from "lucide-vue-next";

interface Props {
  productSlug: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  submitted: [];
}>();

const isOpen = defineModel<boolean>("open", { default: false });
const client = useSanctumClient();
const toast = useToast();
const { t } = useI18n();

const ratingLabels = ["Погано", "Так собі", "Норм", "Добре", "Чудово"];

interface ReviewForm {
  rating: number;
  advantages: string;
  disadvantages: string;
  comment: string;
  youtube_urls: string[];
  author_name: string;
  author_email: string;
  notify_on_reply: boolean;
}

const form = reactive<ReviewForm>({
  rating: 0,
  advantages: "",
  disadvantages: "",
  comment: "",
  youtube_urls: [""],
  author_name: "",
  author_email: "",
  notify_on_reply: false,
});

const errors = reactive({
  rating: "",
  author_name: "",
  author_email: "",
});

const hoverRating = ref(0);
const isSubmitting = ref(false);
const imageFiles = ref<File[]>([]);
const imagePreviews = ref<{ url: string; file: File }[]>([]);

const addYoutubeUrl = () => {
  if (form.youtube_urls.length < 5) {
    form.youtube_urls.push("");
  }
};

const removeYoutubeUrl = (index: number) => {
  form.youtube_urls.splice(index, 1);
};

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const files = target.files;

  if (!files) return;

  const remaining = 10 - imagePreviews.value.length;
  const filesToAdd = Array.from(files).slice(0, remaining);

  filesToAdd.forEach((file) => {
    if (file.size > 5 * 1024 * 1024) {
      toast.add({
        title: t("reviews.form.error"),
        description: t("reviews.form.fileTooLarge", { name: file.name }),
        color: "error",
      });
      return;
    }

    const url = URL.createObjectURL(file);
    imagePreviews.value.push({ url, file });
    imageFiles.value.push(file);
  });

  target.value = "";
};

const removeImage = (index: number) => {
  URL.revokeObjectURL(imagePreviews.value[index].url);
  imagePreviews.value.splice(index, 1);
  imageFiles.value.splice(index, 1);
};

const validate = (): boolean => {
  let isValid = true;
  errors.rating = "";
  errors.author_name = "";
  errors.author_email = "";

  if (form.rating === 0) {
    errors.rating = "Будь ласка, оцініть товар";
    isValid = false;
  }

  if (!form.author_name.trim()) {
    errors.author_name = "Введіть ваше ім'я";
    isValid = false;
  }

  if (!form.author_email.trim()) {
    errors.author_email = "Введіть email";
    isValid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.author_email)) {
    errors.author_email = "Невірний формат email";
    isValid = false;
  }

  return isValid;
};

const uploadImages = async (): Promise<number[]> => {
  const fileIds: number[] = [];

  for (const file of imageFiles.value) {
    const formData = new FormData();
    formData.append("file", file);
    formData.append("folder", "reviews");

    try {
      const response = await client<{ success: boolean; data: { id: number } }>(
        "/api/admin/files",
        {
          method: "POST",
          body: formData,
        }
      );
      if (response.data?.id) {
        fileIds.push(response.data.id);
      }
    } catch (e) {
      console.error("Failed to upload image:", e);
    }
  }

  return fileIds;
};

const submitReview = async () => {
  if (!validate()) return;

  isSubmitting.value = true;

  try {
    // Upload images first
    let imageIds: number[] = [];
    if (imageFiles.value.length > 0) {
      imageIds = await uploadImages();
    }

    // Filter empty youtube URLs
    const youtubeUrls = form.youtube_urls.filter((url) => url.trim() !== "");

    // Submit review
    await client(`/api/products/${props.productSlug}/reviews`, {
      method: "POST",
      body: {
        rating: form.rating,
        advantages: form.advantages || null,
        disadvantages: form.disadvantages || null,
        comment: form.comment || null,
        youtube_urls: youtubeUrls.length > 0 ? youtubeUrls : null,
        image_ids: imageIds.length > 0 ? imageIds : null,
        author_name: form.author_name,
        author_email: form.author_email,
        notify_on_reply: form.notify_on_reply,
      },
    });

    // Reset form
    resetForm();
    emit("submitted");
    close();

    toast.add({
      title: t("reviews.form.successTitle"),
      description: t("reviews.form.successMessage"),
      color: "success",
    });
  } catch (e) {
    console.error("Failed to submit review:", e);
    toast.add({
      title: t("reviews.form.errorTitle"),
      description: t("reviews.form.errorMessage"),
      color: "error",
    });
  } finally {
    isSubmitting.value = false;
  }
};

const resetForm = () => {
  form.rating = 0;
  form.advantages = "";
  form.disadvantages = "";
  form.comment = "";
  form.youtube_urls = [""];
  form.author_name = "";
  form.author_email = "";
  form.notify_on_reply = false;

  imagePreviews.value.forEach((preview) => URL.revokeObjectURL(preview.url));
  imagePreviews.value = [];
  imageFiles.value = [];
};

const close = () => {
  isOpen.value = false;
};

// Cleanup URLs when component unmounts
onUnmounted(() => {
  imagePreviews.value.forEach((preview) => URL.revokeObjectURL(preview.url));
});
</script>
