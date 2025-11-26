<template>
  <VSidebarContent title="Створити атрибут">
    <template #toolbar>
      <div class="flex items-center gap-2 w-full">
        <UButton
          to="/products/attributes"
          variant="ghost"
          color="neutral"
        >
          <template #leading>
            <ArrowLeft class="w-5 h-5" />
          </template>
          Повернутись до атрибутів
        </UButton>
      </div>
    </template>

    <VAttributeForm
      :attribute="null"
      @cancel="router.push('/products/attributes')"
      @success="router.push('/products/attributes')"
    />
  </VSidebarContent>
</template>

<script setup lang="ts">
import { ArrowLeft } from "lucide-vue-next";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VAttributeForm from "~/components/products/attributes/forms/VAttributeForm.vue";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Create Attribute"],
});

const router = useRouter();
</script>
