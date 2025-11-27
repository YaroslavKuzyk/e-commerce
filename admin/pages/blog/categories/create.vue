<template>
  <VSidebarContent title="Створити категорію блогу">
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

    <VBlogCategoryForm
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
  requiredPermissions: ["Create Blog Category"],
});

const router = useRouter();

const handleSuccess = () => {
  router.push('/blog/categories');
};
</script>
