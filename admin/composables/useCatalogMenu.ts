import { CatalogMenuService } from '~/services/CatalogMenuService';
import type {
  CatalogMenuSectionFormData,
  CatalogMenuItemFormData,
} from '~/models/catalogMenu';

export const useCatalogMenu = () => {
  const provider = new CatalogMenuService();

  return {
    getMenuByCategoryId: (categoryId: number) => provider.getMenuByCategoryId(categoryId),
    createOrUpdateMenu: (categoryId: number, data: { is_enabled: boolean }) => provider.createOrUpdateMenu(categoryId, data),
    deleteMenu: (categoryId: number) => provider.deleteMenu(categoryId),

    addSection: (menuId: number, data: CatalogMenuSectionFormData) => provider.addSection(menuId, data),
    updateSection: (sectionId: number, data: Partial<CatalogMenuSectionFormData>) => provider.updateSection(sectionId, data),
    deleteSection: (sectionId: number) => provider.deleteSection(sectionId),
    reorderSections: (menuId: number, column: number, sectionIds: number[]) => provider.reorderSections(menuId, column, sectionIds),

    addItem: (sectionId: number, data: CatalogMenuItemFormData) => provider.addItem(sectionId, data),
    updateItem: (itemId: number, data: Partial<CatalogMenuItemFormData>) => provider.updateItem(itemId, data),
    deleteItem: (itemId: number) => provider.deleteItem(itemId),
    reorderItems: (sectionId: number, itemIds: number[]) => provider.reorderItems(sectionId, itemIds),
  };
};
