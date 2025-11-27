<template>
  <VSidebarContent title="Редагувати статтю блогу">
    <template #toolbar>
      <UButton
        to="/blog/posts"
        variant="ghost"
        color="neutral"
      >
        <template #leading>
          <ArrowLeft class="w-5 h-5" />
        </template>
        Повернутись до статей
      </UButton>
    </template>

    <div v-if="pending" class="flex items-center justify-center py-12">
      <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin" />
    </div>

    <div v-else-if="error" class="text-center py-12">
      <p class="text-error-500">Не вдалося завантажити статтю</p>
      <UButton
        class="mt-4"
        variant="outline"
        @click="refresh"
      >
        Спробувати знову
      </UButton>
    </div>

    <VBlogPostForm
      v-else-if="post"
      :post="post"
      @cancel="router.push('/blog/posts')"
      @success="handleSuccess"
    />
  </VSidebarContent>
</template>

<script setup lang="ts">
import { ArrowLeft } from "lucide-vue-next";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VBlogPostForm from "~/components/blog/posts/forms/VBlogPostForm.vue";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Update Blog Post"],
});

const router = useRouter();
const route = useRoute();
const blogPostStore = useBlogPostStore();

const postId = computed(() => Number(route.params.id));

const { data: postData, pending, error, refresh } = await blogPostStore.fetchBlogPostById(postId.value);

const post = computed(() => postData.value);

const handleSuccess = async () => {
  await refresh();
};
</script>
