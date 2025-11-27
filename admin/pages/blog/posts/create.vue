<template>
  <VSidebarContent title="Створити статтю блогу">
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

    <VBlogPostForm
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
  requiredPermissions: ["Create Blog Post"],
});

const router = useRouter();

const handleSuccess = () => {
  router.push('/blog/posts');
};
</script>
