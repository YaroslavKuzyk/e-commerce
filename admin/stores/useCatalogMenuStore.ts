import { defineStore } from 'pinia';
import type {
  CatalogMenuSectionFormData,
  CatalogMenuItemFormData,
} from '~/models/catalogMenu';

export const useCatalogMenuStore = defineStore('catalogMenu', () => {
  const {
    getMenuByCategoryId,
    createOrUpdateMenu,
    deleteMenu,
    addSection,
    updateSection,
    deleteSection,
    reorderSections,
    addItem,
    updateItem,
    deleteItem,
    reorderItems,
  } = useCatalogMenu();

  return {
    fetchMenuByCategoryId: async (categoryId: number) => await getMenuByCategoryId(categoryId),
    onCreateOrUpdateMenu: async (categoryId: number, data: { is_enabled: boolean }) => await createOrUpdateMenu(categoryId, data),
    onDeleteMenu: async (categoryId: number) => await deleteMenu(categoryId),

    onAddSection: async (menuId: number, data: CatalogMenuSectionFormData) => await addSection(menuId, data),
    onUpdateSection: async (sectionId: number, data: Partial<CatalogMenuSectionFormData>) => await updateSection(sectionId, data),
    onDeleteSection: async (sectionId: number) => await deleteSection(sectionId),
    onReorderSections: async (menuId: number, column: number, sectionIds: number[]) => await reorderSections(menuId, column, sectionIds),

    onAddItem: async (sectionId: number, data: CatalogMenuItemFormData) => await addItem(sectionId, data),
    onUpdateItem: async (itemId: number, data: Partial<CatalogMenuItemFormData>) => await updateItem(itemId, data),
    onDeleteItem: async (itemId: number) => await deleteItem(itemId),
    onReorderItems: async (sectionId: number, itemIds: number[]) => await reorderItems(sectionId, itemIds),
  };
});
