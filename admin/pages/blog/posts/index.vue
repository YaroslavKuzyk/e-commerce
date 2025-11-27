<template>
  <VSidebarContent title="Статті блогу">
    <template #toolbar>
      <div class="flex items-center justify-between w-full gap-2">
        <div class="flex items-center gap-2 w-full flex-wrap">
          <UInput
            v-model="filters.title"
            placeholder="Пошук за назвою"
            class="w-[200px]"
          >
            <template #leading>
              <Search class="w-5 h-5" />
            </template>
          </UInput>
          <USelectMenu
            v-model="filters.status"
            :items="statusOptions"
            placeholder="Статус"
            value-key="value"
            label-key="label"
            class="w-[150px]"
          >
            <template #trailing>
              <UButton
                v-if="filters.status"
                size="sm"
                variant="link"
                aria-label="Очистити"
                @click.stop="filters.status = null"
                color="neutral"
              >
                <CircleX class="w-4 h-4" />
              </UButton>
            </template>
          </USelectMenu>
          <USelectMenu
            v-model="filters.blog_category_id"
            :items="categoryOptions"
            placeholder="Категорія"
            value-key="value"
            label-key="label"
            class="w-[180px]"
          >
            <template #trailing>
              <UButton
                v-if="filters.blog_category_id"
                size="sm"
                variant="link"
                aria-label="Очистити"
                @click.stop="filters.blog_category_id = null"
                color="neutral"
              >
                <CircleX class="w-4 h-4" />
              </UButton>
            </template>
          </USelectMenu>
          <UButton
            v-if="hasActiveFilters"
            variant="ghost"
            @click="clearFilters"
          >
            <template #leading>
              <X class="w-5 h-5" />
            </template>
            Очистити фільтри
          </UButton>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <HasPermissions :required-permissions="['Create Blog Post']">
            <UButton @click="router.push('/blog/posts/create')">
              <template #leading>
                <Plus class="w-4 h-4" />
              </template>
              Додати статтю
            </UButton>
          </HasPermissions>
        </div>
      </div>
    </template>

    <HasPermissions :required-permissions="['Read Blog Posts']">
      <!-- Delete Modal -->
      <UModal v-model:open="isDeleteModalOpen" title="Видалити статтю">
        <template #body>
          <div class="space-y-4 p-4">
            <div v-if="postToDelete" class="p-4 bg-error-50 dark:bg-error-900/20 rounded-lg">
              <p class="text-sm text-error-600 dark:text-error-400">
                Ви збираєтеся видалити статтю:
              </p>
              <p class="font-semibold mt-2">{{ postToDelete.title }}</p>
            </div>

            <p class="text-sm text-gray-600 dark:text-gray-400">
              Ця дія незворотна.
            </p>

            <div class="flex justify-end gap-2">
              <UButton
                variant="outline"
                color="neutral"
                @click="isDeleteModalOpen = false"
              >
                Скасувати
              </UButton>
              <UButton
                color="error"
                :loading="deleteLoading"
                @click="handleDeletePost"
              >
                Видалити
              </UButton>
            </div>
          </div>
        </template>
      </UModal>

      <UTable
        :data="postsData || []"
        :columns="columns"
        :loading="pending"
      >
        <template #title-cell="{ row }">
          <div class="flex items-center gap-3">
            <div
              v-if="row.original.preview_image"
              class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800 flex-shrink-0"
            >
              <VSecureImage
                :file-id="row.original.preview_image_id"
                :alt="row.original.title"
                width="w-12"
                height="h-12"
                object-fit="cover"
              />
            </div>
            <div
              v-else
              class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center flex-shrink-0"
            >
              <UIcon name="i-heroicons-photo" class="w-6 h-6 text-gray-400" />
            </div>
            <div>
              <p class="font-medium text-gray-900 dark:text-white">{{ row.original.title }}</p>
              <p class="text-sm text-gray-500">{{ row.original.slug }}</p>
            </div>
          </div>
        </template>

        <template #category-cell="{ row }">
          <UBadge v-if="row.original.category" variant="subtle">
            {{ row.original.category.name }}
          </UBadge>
          <span v-else class="text-gray-400">—</span>
        </template>

        <template #status-cell="{ row }">
          <UBadge
            :color="row.original.status === 'published' ? 'success' : 'warning'"
            variant="subtle"
          >
            {{ row.original.status === 'published' ? 'Опубліковано' : 'Чернетка' }}
          </UBadge>
        </template>

        <template #publication_date-cell="{ row }">
          <span v-if="row.original.publication_date" class="text-gray-600 dark:text-gray-400">
            {{ formatDate(row.original.publication_date) }}
          </span>
          <span v-else class="text-gray-400">—</span>
        </template>

        <template #actions-cell="{ row }">
          <div class="flex items-center gap-1">
            <HasPermissions :required-permissions="['Update Blog Post']">
              <UButton
                size="sm"
                variant="ghost"
                icon="i-heroicons-pencil"
                @click="handleEdit(row.original)"
              />
            </HasPermissions>
            <HasPermissions :required-permissions="['Delete Blog Post']">
              <UButton
                size="sm"
                variant="ghost"
                color="error"
                icon="i-heroicons-trash"
                @click="handleDelete(row.original)"
              />
            </HasPermissions>
          </div>
        </template>
      </UTable>
    </HasPermissions>

    <template #pagination>
      <VPagination
        :meta="meta"
        @update:page="(page) => filters.page = page"
        @update:per-page="(perPage) => filters.per_page = perPage"
      />
    </template>
  </VSidebarContent>
</template>

<script setup lang="ts">
import { Search, CircleX, X, Plus } from "lucide-vue-next";
import HasPermissions from "~/components/common/VHasPermissions.vue";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VPagination from "~/components/common/VPagination.vue";
import type { BlogPost, BlogPostFilters, BlogPostStatus } from "~/models/blogPost";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Read Blog Posts"],
});

const router = useRouter();
const blogPostStore = useBlogPostStore();
const blogCategoryStore = useBlogCategoryStore();
const toast = useToast();

const isDeleteModalOpen = ref(false);
const postToDelete = ref<BlogPost | null>(null);
const deleteLoading = ref(false);

const filters = ref({
  title: "",
  status: null as BlogPostStatus | null,
  blog_category_id: null as number | null,
  page: 1,
  per_page: 15,
});

const statusOptions = [
  { label: "Опубліковано", value: "published" },
  { label: "Чернетка", value: "draft" },
];

// Fetch categories for filter
const { data: categoriesData } = await blogCategoryStore.fetchBlogCategories();

const categoryOptions = computed(() => {
  return (categoriesData.value?.data || []).map(cat => ({
    label: cat.name,
    value: cat.id,
  }));
});

const columns = [
  { id: 'title', header: 'Назва' },
  { id: 'category', header: 'Категорія', meta: { class: { th: 'w-[150px]' } } },
  { id: 'status', header: 'Статус', meta: { class: { th: 'w-[120px]' } } },
  { id: 'publication_date', header: 'Дата публікації', meta: { class: { th: 'w-[150px]' } } },
  { id: 'actions', header: '', meta: { class: { th: 'w-24' } } },
];

const {
  data: postsData,
  meta,
  pending,
  hasActiveFilters,
  clearFilters,
  refresh: internalRefresh,
} = await usePaginationList<typeof filters.value, BlogPost>({
  key: 'blog-posts-list',
  filters,
  fetchFunction: (filters?: BlogPostFilters) => blogPostStore.fetchBlogPostsPromise(filters),
  debounceFields: ["title"],
});

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('uk-UA', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const refreshPosts = async () => {
  await internalRefresh();
};

const handleEdit = (post: BlogPost) => {
  router.push(`/blog/posts/${post.id}/edit`);
};

const handleDelete = (post: BlogPost) => {
  postToDelete.value = post;
  isDeleteModalOpen.value = true;
};

const handleDeletePost = async () => {
  if (!postToDelete.value) return;

  deleteLoading.value = true;
  try {
    const { error } = await blogPostStore.onDeleteBlogPost(postToDelete.value.id);

    if (error.value) {
      toast.add({
        title: "Помилка",
        description: "Не вдалося видалити статтю",
        color: "error",
      });
      return;
    }

    toast.add({
      title: "Успішно",
      description: "Статтю видалено",
      color: "success",
    });

    isDeleteModalOpen.value = false;
    postToDelete.value = null;
    await refreshPosts();
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося видалити статтю",
      color: "error",
    });
  } finally {
    deleteLoading.value = false;
  }
};
</script>
