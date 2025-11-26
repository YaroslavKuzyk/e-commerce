<template>
  <VSidebarContent title="Редагувати атрибут">
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

    <div v-if="pending" class="flex items-center justify-center py-12">
      <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 animate-spin" />
    </div>

    <div v-else-if="error" class="text-center py-12">
      <p class="text-error-500">Не вдалося завантажити атрибут</p>
      <UButton
        class="mt-4"
        variant="outline"
        @click="refresh"
      >
        Спробувати знову
      </UButton>
    </div>

    <VAttributeForm
      v-else-if="attribute"
      :attribute="attribute"
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
  requiredPermissions: ["Update Attribute"],
});

const router = useRouter();
const route = useRoute();
const attributeStore = useAttributeStore();

const attributeId = computed(() => Number(route.params.id));

const { data: attributeData, pending, error, refresh } = await attributeStore.fetchAttributeById(attributeId.value);

const attribute = computed(() => attributeData.value);
</script>
