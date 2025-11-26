<template>
  <VSidebarContent title="Редагувати бренд">
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

    <div v-if="brandPending" class="flex items-center justify-center py-12">
      <Loader2 class="w-8 h-8 animate-spin text-gray-400" />
    </div>

    <div v-else-if="brandError" class="text-center py-12">
      <p class="text-error-500">Не вдалося завантажити бренд</p>
    </div>

    <VProductBrandForm
      v-else-if="brand"
      :brand="brand"
      @cancel="router.push('/products/brands')"
      @success="router.push('/products/brands')"
    />
  </VSidebarContent>
</template>

<script setup lang="ts">
import { ArrowLeft, Loader2 } from "lucide-vue-next";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import VProductBrandForm from "~/components/products/brands/forms/VProductBrandForm.vue";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Update Product Brand"],
});

const router = useRouter();
const route = useRoute();
const productBrandStore = useProductBrandStore();

const brandId = computed(() => Number(route.params.id));

const {
  data: brand,
  pending: brandPending,
  error: brandError,
} = await productBrandStore.fetchProductBrandById(brandId.value);
</script>
