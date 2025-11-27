<template>
  <VSidebarContent title="Категорії блогу">
    <template #toolbar>
      <div class="flex items-center justify-between w-full gap-2">
        <div class="flex items-center gap-2 w-full flex-wrap">
          <UInput
            v-model="filters.name"
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
          <HasPermissions :required-permissions="['Create Blog Category']">
            <UButton @click="router.push('/blog/categories/create')">
              <template #leading>
                <Plus class="w-4 h-4" />
              </template>
              Додати категорію
            </UButton>
          </HasPermissions>
        </div>
      </div>
    </template>

    <HasPermissions :required-permissions="['Read Blog Categories']">
      <!-- Delete Modal -->
      <UModal v-model:open="isDeleteModalOpen" title="Видалити категорію">
        <template #body>
          <div class="space-y-4 p-4">
            <div v-if="categoryToDelete" class="p-4 bg-error-50 dark:bg-error-900/20 rounded-lg">
              <p class="text-sm text-error-600 dark:text-error-400">
                Ви збираєтеся видалити категорію:
              </p>
              <p class="font-semibold mt-2">{{ categoryToDelete.name }}</p>
            </div>

            <p class="text-sm text-gray-600 dark:text-gray-400">
              Ця дія незворотна. Категорію неможливо видалити, якщо в ній є статті.
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
                @click="handleDeleteCategory"
              >
                Видалити
              </UButton>
            </div>
          </div>
        </template>
      </UModal>

      <UTable
        :data="categoriesData || []"
        :columns="columns"
        :loading="pending"
      >
        <template #sort_order-cell="{ row }">
          <span class="text-gray-500">{{ row.original.sort_order }}</span>
        </template>

        <template #name-cell="{ row }">
          <span class="font-medium">{{ row.original.name }}</span>
        </template>

        <template #slug-cell="{ row }">
          <span class="text-gray-500">{{ row.original.slug }}</span>
        </template>

        <template #status-cell="{ row }">
          <UBadge
            :color="row.original.status === 'published' ? 'success' : 'warning'"
            variant="subtle"
          >
            {{ row.original.status === 'published' ? 'Опубліковано' : 'Чернетка' }}
          </UBadge>
        </template>

        <template #posts_count-cell="{ row }">
          <span class="text-gray-600 dark:text-gray-400">{{ row.original.posts_count || 0 }}</span>
        </template>

        <template #actions-cell="{ row }">
          <div class="flex items-center gap-1">
            <HasPermissions :required-permissions="['Update Blog Category']">
              <UButton
                size="sm"
                variant="ghost"
                icon="i-heroicons-pencil"
                @click="handleEdit(row.original)"
              />
            </HasPermissions>
            <HasPermissions :required-permissions="['Delete Blog Category']">
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
import type { BlogCategory, BlogCategoryFilters, BlogCategoryStatus } from "~/models/blogCategory";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Read Blog Categories"],
});

const router = useRouter();
const blogCategoryStore = useBlogCategoryStore();
const toast = useToast();

const isDeleteModalOpen = ref(false);
const categoryToDelete = ref<BlogCategory | null>(null);
const deleteLoading = ref(false);

const filters = ref({
  name: "",
  status: null as BlogCategoryStatus | null,
  page: 1,
  per_page: 15,
});

const statusOptions = [
  { label: "Опубліковано", value: "published" },
  { label: "Чернетка", value: "draft" },
];

const columns = [
  { id: 'sort_order', header: '#', meta: { class: { th: 'w-16' } } },
  { id: 'name', header: 'Назва' },
  { id: 'slug', header: 'Slug' },
  { id: 'status', header: 'Статус', meta: { class: { th: 'w-[120px]' } } },
  { id: 'posts_count', header: 'Статей', meta: { class: { th: 'w-[100px]' } } },
  { id: 'actions', header: '', meta: { class: { th: 'w-24' } } },
];

const {
  data: categoriesData,
  meta,
  pending,
  hasActiveFilters,
  clearFilters,
  refresh: internalRefresh,
} = await usePaginationList<typeof filters.value, BlogCategory>({
  key: 'blog-categories-list',
  filters,
  fetchFunction: (filters?: BlogCategoryFilters) => blogCategoryStore.fetchBlogCategoriesPromise(filters),
  debounceFields: ["name"],
});

const refreshCategories = async () => {
  await internalRefresh();
};

const handleEdit = (category: BlogCategory) => {
  router.push(`/blog/categories/${category.id}/edit`);
};

const handleDelete = (category: BlogCategory) => {
  categoryToDelete.value = category;
  isDeleteModalOpen.value = true;
};

const handleDeleteCategory = async () => {
  if (!categoryToDelete.value) return;

  deleteLoading.value = true;
  try {
    const { error } = await blogCategoryStore.onDeleteBlogCategory(categoryToDelete.value.id);

    if (error.value) {
      toast.add({
        title: "Помилка",
        description: error.value.message || "Не вдалося видалити категорію",
        color: "error",
      });
      return;
    }

    toast.add({
      title: "Успішно",
      description: "Категорію видалено",
      color: "success",
    });

    isDeleteModalOpen.value = false;
    categoryToDelete.value = null;
    await refreshCategories();
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося видалити категорію",
      color: "error",
    });
  } finally {
    deleteLoading.value = false;
  }
};
</script>
