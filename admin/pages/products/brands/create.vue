<template>
  <VSidebarContent title="Створити бренд">
    <template #toolbar>
      <div class="flex items-center gap-2 w-full">
        <UButton
          to="/products/brands"
          variant="ghost"
          color="neutral"
        >
          <template #leading>
            <ArrowLeft class="w-5 h-5" />
          </template>
          Повернутись до брендів
        </UButton>
      </div>
    </template>

    <VProductBrandForm
      :brand="null"
      @cancel="router.push('/products/brands')"
      @success="router.push('/products/brands')"
    />
  </VSidebarContent>
</template>

<script setup lang="ts">
import { ArrowLeft } from "lucide-vue-next";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VProductBrandForm from "~/components/products/brands/forms/VProductBrandForm.vue";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Create Product Brand"],
});

const router = useRouter();
</script>
