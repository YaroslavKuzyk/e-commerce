<template>
  <VSidebarContent title="Створити продукт">
    <template #toolbar>
      <div class="flex items-center gap-2 w-full justify-between">
        <UButton
          to="/products/list"
          variant="ghost"
          color="neutral"
        >
          <template #leading>
            <ArrowLeft class="w-5 h-5" />
          </template>
          Повернутись до продуктів
        </UButton>
        <UTabs
          model-value="info"
          :items="tabItems"
          class="gap-0"
        />
      </div>
    </template>

    <VProductForm
      :product="null"
      @cancel="router.push('/products/list')"
      @success="router.push('/products/list')"
      @created="handleCreated"
    />
  </VSidebarContent>
</template>

<script setup lang="ts">
import { ArrowLeft } from "lucide-vue-next";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VProductForm from "~/components/products/list/forms/VProductForm.vue";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Create Product"],
});

const router = useRouter();

const disabledTooltip = "Буде доступно після створення продукту. Ви можете створити продукт як чернетку, додати дані і потім опублікувати.";

const tabItems = [
  {
    value: 'info',
    label: 'Основна інформація',
    icon: 'i-heroicons-information-circle',
  },
  {
    value: 'specifications',
    label: 'Характеристики',
    icon: 'i-heroicons-list-bullet',
    disabled: true,
    tooltip: disabledTooltip,
  },
  {
    value: 'variants',
    label: 'Варіації',
    icon: 'i-heroicons-squares-2x2',
    disabled: true,
    tooltip: disabledTooltip,
  },
];

const handleCreated = (productId: number) => {
  router.push(`/products/list/${productId}/edit?tab=specifications`);
};
</script>
