<template>
  <VSidebarContent title="Редагувати категорію блогу">
    <template #toolbar>
      <UButton
        to="/blog/categories"
        variant="ghost"
        color="neutral"
      >
        <template #leading>
          <ArrowLeft class="w-5 h-5" />
        </template>
        Повернутись до категорій
      </UButton>
    </template>

    <div v-if="pending" class="flex items-center justify-center py-12">
      <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin" />
    </div>

    <div v-else-if="error" class="text-center py-12">
      <p class="text-error-500">Не вдалося завантажити категорію</p>
      <UButton
        class="mt-4"
        variant="outline"
        @click="refresh"
      >
        Спробувати знову
      </UButton>
    </div>

    <VBlogCategoryForm
      v-else-if="category"
      :category="category"
      @cancel="router.push('/blog/categories')"
      @success="handleSuccess"
    />
  </VSidebarContent>
</template>

<script setup lang="ts">
import { ArrowLeft } from "lucide-vue-next";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VBlogCategoryForm from "~/components/blog/categories/forms/VBlogCategoryForm.vue";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Update Blog Category"],
});

const router = useRouter();
const route = useRoute();
const blogCategoryStore = useBlogCategoryStore();

const categoryId = computed(() => Number(route.params.id));

const { data: categoryData, pending, error, refresh } = await blogCategoryStore.fetchBlogCategoryById(categoryId.value);

const category = computed(() => categoryData.value);

const handleSuccess = async () => {
  await refresh();
};
</script>
