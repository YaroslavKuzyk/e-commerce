<template>
  <VSidebarContent :title="$t('attributes.title')">
    <template #toolbar>
      <div class="flex items-center justify-between w-full gap-2">
        <div class="flex items-center gap-2 w-full">
          <UInput
            v-model="filters.name"
            :placeholder="$t('attributes.searchByName')"
            class="w-[200px]"
          >
            <template #leading>
              <Search class="w-5 h-5" />
            </template>
          </UInput>
          <UInput
            v-model="filters.slug"
            :placeholder="$t('attributes.searchBySlug')"
            class="w-[200px]"
          >
            <template #leading>
              <Hash class="w-5 h-5" />
            </template>
          </UInput>
          <USelectMenu
            v-model="filters.type"
            :items="typeOptions"
            :placeholder="$t('attributes.type')"
            value-key="value"
            label-key="label"
            class="w-[180px]"
          >
            <template #trailing>
              <UButton
                v-if="filters.type"
                size="sm"
                variant="link"
                :aria-label="$t('common.clear')"
                @click.stop="filters.type = null"
                color="neutral"
              >
                <CircleX class="w-4 h-4" />
              </UButton>
            </template>
          </USelectMenu>
          <USelectMenu
            v-model="filters.status"
            :items="statusOptions"
            :placeholder="$t('common.status')"
            value-key="value"
            label-key="label"
            class="w-[150px]"
          >
            <template #trailing>
              <UButton
                v-if="filters.status"
                size="sm"
                variant="link"
                :aria-label="$t('common.clear')"
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
            {{ $t("common.clearFilters") }}
          </UButton>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <HasPermissions :required-permissions="['Create Attribute']">
            <UButton @click="router.push('/products/attributes/create')">
              <template #leading>
                <Plus class="w-4 h-4" />
              </template>
              {{ $t("attributes.add") }}
            </UButton>
          </HasPermissions>
        </div>
      </div>
    </template>

    <HasPermissions :required-permissions="['Read Attributes']">
      <!-- Delete Modal -->
      <VAttributeDeleteModal
        v-model:is-open="isDeleteModalOpen"
        :attribute="attributeToDelete"
        @refresh="refreshAttributes"
      />

      <AttributesTable
        :attributes="attributesData || []"
        :loading="pending"
        @edit="handleEdit"
        @delete="handleDelete"
      />
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
import { Search, Hash, CircleX, X, Plus } from "lucide-vue-next";
import HasPermissions from "~/components/common/VHasPermissions.vue";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";
import AttributesTable from "~/components/products/attributes/tables/AttributesTable.vue";
import VAttributeDeleteModal from "~/components/products/attributes/modals/VAttributeDeleteModal.vue";
import VPagination from "~/components/common/VPagination.vue";
import type { Attribute, AttributeFilters, AttributeStatus, AttributeType } from "~/models/attribute";

definePageMeta({
  middleware: ["sanctum:auth", "permissions"],
  requiredPermissions: ["Read Attributes"],
});

const { t } = useI18n();
const router = useRouter();
const attributeStore = useAttributeStore();

const isDeleteModalOpen = ref(false);
const attributeToDelete = ref<Attribute | null>(null);

const filters = ref({
  name: "",
  slug: "",
  type: null as AttributeType | null,
  status: null as AttributeStatus | null,
  page: 1,
  per_page: 15,
});

const typeOptions = computed(() => [
  { label: t("attributes.typeSelect"), value: "select" },
  { label: t("attributes.typeMultiSelect"), value: "multi_select" },
  { label: t("attributes.typeCheckbox"), value: "checkbox" },
  { label: t("attributes.typeSwitch"), value: "switch" },
]);

const statusOptions = computed(() => [
  { label: t("common.published"), value: "published" },
  { label: t("common.draft"), value: "draft" },
]);

const {
  data: attributesData,
  meta,
  pending,
  hasActiveFilters,
  clearFilters,
  refresh: internalRefresh,
} = await usePaginationList<typeof filters.value, Attribute>({
  key: 'attributes-list',
  filters,
  fetchFunction: (filters?: AttributeFilters) => attributeStore.fetchAttributesPromise(filters),
  debounceFields: ["name", "slug"],
});

const refreshAttributes = async () => {
  await internalRefresh();
};

const handleEdit = (attribute: Attribute) => {
  router.push(`/products/attributes/${attribute.id}/edit`);
};

const handleDelete = (attribute: Attribute) => {
  attributeToDelete.value = attribute;
  isDeleteModalOpen.value = true;
};
</script>
