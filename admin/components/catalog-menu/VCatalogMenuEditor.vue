<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="font-medium text-lg">Структура меню</h3>
    </div>

    <!-- 3 Columns -->
    <div class="grid grid-cols-3 gap-4">
      <VCatalogMenuColumn
        v-for="column in [1, 2, 3]"
        :key="column"
        :column="column"
        :sections="getSectionsByColumn(column)"
        :menu-id="menu.id"
        @add-section="openAddSectionModal"
        @edit-section="openEditSectionModal"
        @delete-section="openDeleteSectionModal"
        @add-item="openAddItemModal"
        @edit-item="openEditItemModal"
        @delete-item="openDeleteItemModal"
        @reorder-sections="handleReorderSections"
        @reorder-items="handleReorderItems"
      />
    </div>

    <!-- Section Modal -->
    <VSectionFormModal
      v-model="isSectionModalOpen"
      :section="editingSection"
      :column-index="selectedColumn"
      :is-loading="sectionLoading"
      @submit="handleSectionSubmit"
    />

    <!-- Item Modal -->
    <VItemFormModal
      v-model="isItemModalOpen"
      :item="editingItem"
      :is-loading="itemLoading"
      @submit="handleItemSubmit"
    />

    <!-- Delete Section Modal -->
    <UModal v-model:open="isDeleteSectionModalOpen" title="Видалити секцію">
      <template #body>
        <div class="space-y-4 p-4">
          <div v-if="deletingSection" class="p-4 bg-error-50 dark:bg-error-900/20 rounded-lg">
            <p class="text-sm text-error-600 dark:text-error-400">
              Ви збираєтеся видалити секцію:
            </p>
            <p class="font-semibold mt-2">{{ deletingSection.name }}</p>
            <p v-if="deletingSection.items.length > 0" class="text-sm mt-1">
              Буде видалено також {{ deletingSection.items.length }} посилань
            </p>
          </div>

          <p class="text-sm text-gray-600 dark:text-gray-400">
            Ця дія незворотна.
          </p>

          <div class="flex justify-end gap-2">
            <UButton
              variant="outline"
              color="neutral"
              @click="isDeleteSectionModalOpen = false"
            >
              Скасувати
            </UButton>
            <UButton
              color="error"
              :loading="deleteSectionLoading"
              @click="handleDeleteSection"
            >
              Видалити
            </UButton>
          </div>
        </div>
      </template>
    </UModal>

    <!-- Delete Item Modal -->
    <UModal v-model:open="isDeleteItemModalOpen" title="Видалити посилання">
      <template #body>
        <div class="space-y-4 p-4">
          <div v-if="deletingItem" class="p-4 bg-error-50 dark:bg-error-900/20 rounded-lg">
            <p class="text-sm text-error-600 dark:text-error-400">
              Ви збираєтеся видалити посилання:
            </p>
            <p class="font-semibold mt-2">{{ deletingItem.name }}</p>
          </div>

          <p class="text-sm text-gray-600 dark:text-gray-400">
            Ця дія незворотна.
          </p>

          <div class="flex justify-end gap-2">
            <UButton
              variant="outline"
              color="neutral"
              @click="isDeleteItemModalOpen = false"
            >
              Скасувати
            </UButton>
            <UButton
              color="error"
              :loading="deleteItemLoading"
              @click="handleDeleteItem"
            >
              Видалити
            </UButton>
          </div>
        </div>
      </template>
    </UModal>
  </div>
</template>

<script setup lang="ts">
import VCatalogMenuColumn from "./VCatalogMenuColumn.vue";
import VSectionFormModal from "./modals/VSectionFormModal.vue";
import VItemFormModal from "./modals/VItemFormModal.vue";
import type {
  CatalogMenu,
  CatalogMenuSection,
  CatalogMenuItem,
  CatalogMenuSectionFormData,
  CatalogMenuItemFormData,
} from "~/models/catalogMenu";

interface Props {
  menu: CatalogMenu;
}

interface Emits {
  (e: "refresh"): void;
}

const props = defineProps<Props>();
const emits = defineEmits<Emits>();
const toast = useToast();
const catalogMenuStore = useCatalogMenuStore();

// Section state
const isSectionModalOpen = ref(false);
const editingSection = ref<CatalogMenuSection | null>(null);
const selectedColumn = ref<1 | 2 | 3>(1);
const sectionLoading = ref(false);
const selectedSectionId = ref<number | null>(null);

const isDeleteSectionModalOpen = ref(false);
const deletingSection = ref<CatalogMenuSection | null>(null);
const deleteSectionLoading = ref(false);

// Item state
const isItemModalOpen = ref(false);
const editingItem = ref<CatalogMenuItem | null>(null);
const itemLoading = ref(false);

const isDeleteItemModalOpen = ref(false);
const deletingItem = ref<CatalogMenuItem | null>(null);
const deleteItemLoading = ref(false);

const getSectionsByColumn = (column: number): CatalogMenuSection[] => {
  return props.menu.sections
    .filter((s) => s.column_index === column)
    .sort((a, b) => a.sort_order - b.sort_order);
};

// Section handlers
const openAddSectionModal = (column: 1 | 2 | 3) => {
  editingSection.value = null;
  selectedColumn.value = column;
  isSectionModalOpen.value = true;
};

const openEditSectionModal = (section: CatalogMenuSection) => {
  editingSection.value = section;
  selectedColumn.value = section.column_index;
  isSectionModalOpen.value = true;
};

const openDeleteSectionModal = (section: CatalogMenuSection) => {
  deletingSection.value = section;
  isDeleteSectionModalOpen.value = true;
};

const handleSectionSubmit = async (data: CatalogMenuSectionFormData) => {
  sectionLoading.value = true;

  try {
    if (editingSection.value) {
      const { error } = await catalogMenuStore.onUpdateSection(editingSection.value.id, data);

      if (error.value) {
        toast.add({
          title: "Помилка",
          description: "Не вдалося оновити секцію",
          color: "error",
        });
        return;
      }

      toast.add({
        title: "Успішно",
        description: "Секцію оновлено",
        color: "success",
      });
    } else {
      const { error } = await catalogMenuStore.onAddSection(props.menu.id, data);

      if (error.value) {
        toast.add({
          title: "Помилка",
          description: "Не вдалося додати секцію",
          color: "error",
        });
        return;
      }

      toast.add({
        title: "Успішно",
        description: "Секцію додано",
        color: "success",
      });
    }

    isSectionModalOpen.value = false;
    emits("refresh");
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося зберегти секцію",
      color: "error",
    });
  } finally {
    sectionLoading.value = false;
  }
};

const handleDeleteSection = async () => {
  if (!deletingSection.value) return;

  deleteSectionLoading.value = true;

  try {
    const { error } = await catalogMenuStore.onDeleteSection(deletingSection.value.id);

    if (error.value) {
      toast.add({
        title: "Помилка",
        description: "Не вдалося видалити секцію",
        color: "error",
      });
      return;
    }

    toast.add({
      title: "Успішно",
      description: "Секцію видалено",
      color: "success",
    });

    isDeleteSectionModalOpen.value = false;
    deletingSection.value = null;
    emits("refresh");
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося видалити секцію",
      color: "error",
    });
  } finally {
    deleteSectionLoading.value = false;
  }
};

const handleReorderSections = async (column: number, sectionIds: number[]) => {
  try {
    const { error } = await catalogMenuStore.onReorderSections(props.menu.id, column, sectionIds);

    if (error.value) {
      toast.add({
        title: "Помилка",
        description: "Не вдалося змінити порядок секцій",
        color: "error",
      });
      emits("refresh");
      return;
    }

    toast.add({
      title: "Успішно",
      description: "Порядок секцій змінено",
      color: "success",
    });

    emits("refresh");
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося змінити порядок секцій",
      color: "error",
    });
    emits("refresh");
  }
};

// Item handlers
const openAddItemModal = (sectionId: number) => {
  editingItem.value = null;
  selectedSectionId.value = sectionId;
  isItemModalOpen.value = true;
};

const openEditItemModal = (item: CatalogMenuItem) => {
  editingItem.value = item;
  selectedSectionId.value = item.catalog_menu_section_id;
  isItemModalOpen.value = true;
};

const openDeleteItemModal = (item: CatalogMenuItem) => {
  deletingItem.value = item;
  isDeleteItemModalOpen.value = true;
};

const handleItemSubmit = async (data: CatalogMenuItemFormData) => {
  itemLoading.value = true;

  try {
    if (editingItem.value) {
      const { error } = await catalogMenuStore.onUpdateItem(editingItem.value.id, data);

      if (error.value) {
        toast.add({
          title: "Помилка",
          description: "Не вдалося оновити посилання",
          color: "error",
        });
        return;
      }

      toast.add({
        title: "Успішно",
        description: "Посилання оновлено",
        color: "success",
      });
    } else {
      if (!selectedSectionId.value) return;

      const { error } = await catalogMenuStore.onAddItem(selectedSectionId.value, data);

      if (error.value) {
        toast.add({
          title: "Помилка",
          description: "Не вдалося додати посилання",
          color: "error",
        });
        return;
      }

      toast.add({
        title: "Успішно",
        description: "Посилання додано",
        color: "success",
      });
    }

    isItemModalOpen.value = false;
    emits("refresh");
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося зберегти посилання",
      color: "error",
    });
  } finally {
    itemLoading.value = false;
  }
};

const handleDeleteItem = async () => {
  if (!deletingItem.value) return;

  deleteItemLoading.value = true;

  try {
    const { error } = await catalogMenuStore.onDeleteItem(deletingItem.value.id);

    if (error.value) {
      toast.add({
        title: "Помилка",
        description: "Не вдалося видалити посилання",
        color: "error",
      });
      return;
    }

    toast.add({
      title: "Успішно",
      description: "Посилання видалено",
      color: "success",
    });

    isDeleteItemModalOpen.value = false;
    deletingItem.value = null;
    emits("refresh");
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося видалити посилання",
      color: "error",
    });
  } finally {
    deleteItemLoading.value = false;
  }
};

const handleReorderItems = async (sectionId: number, itemIds: number[]) => {
  try {
    const { error } = await catalogMenuStore.onReorderItems(sectionId, itemIds);

    if (error.value) {
      toast.add({
        title: "Помилка",
        description: "Не вдалося змінити порядок посилань",
        color: "error",
      });
      emits("refresh");
      return;
    }

    toast.add({
      title: "Успішно",
      description: "Порядок посилань змінено",
      color: "success",
    });

    emits("refresh");
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося змінити порядок посилань",
      color: "error",
    });
    emits("refresh");
  }
};
</script>
