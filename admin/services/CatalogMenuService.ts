import type {
  CatalogMenu,
  CatalogMenuSection,
  CatalogMenuItem,
  CatalogMenuSectionFormData,
  CatalogMenuItemFormData,
  ICatalogMenuProvider,
} from '~/models/catalogMenu';

export class CatalogMenuService implements ICatalogMenuProvider {
  getMenuByCategoryId(categoryId: number) {
    const client = useSanctumClient();

    return useAsyncData<CatalogMenu | null>(
      `catalog-menu-${categoryId}`,
      () =>
        client<{ success: boolean; data: CatalogMenu | null }>(
          `/api/admin/catalog-menus/category/${categoryId}`
        ).then((res) => res.data),
      { immediate: false }
    );
  }

  createOrUpdateMenu(categoryId: number, data: { is_enabled: boolean }) {
    const client = useSanctumClient();

    return useAsyncData<CatalogMenu>(
      `catalog-menu-save-${categoryId}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: CatalogMenu }>(
          `/api/admin/catalog-menus/category/${categoryId}`,
          {
            method: 'POST',
            body: data,
          }
        ).then((res) => res.data)
    );
  }

  deleteMenu(categoryId: number) {
    const client = useSanctumClient();

    return useAsyncData<{ success: boolean }>(
      `catalog-menu-delete-${categoryId}-${Date.now()}`,
      () =>
        client<{ success: boolean }>(
          `/api/admin/catalog-menus/category/${categoryId}`,
          {
            method: 'DELETE',
          }
        ).then((res) => res)
    );
  }

  addSection(menuId: number, data: CatalogMenuSectionFormData) {
    const client = useSanctumClient();

    return useAsyncData<CatalogMenuSection>(
      `catalog-menu-section-add-${menuId}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: CatalogMenuSection }>(
          `/api/admin/catalog-menus/${menuId}/sections`,
          {
            method: 'POST',
            body: data,
          }
        ).then((res) => res.data)
    );
  }

  updateSection(sectionId: number, data: Partial<CatalogMenuSectionFormData>) {
    const client = useSanctumClient();

    return useAsyncData<CatalogMenuSection>(
      `catalog-menu-section-update-${sectionId}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: CatalogMenuSection }>(
          `/api/admin/catalog-menus/sections/${sectionId}`,
          {
            method: 'PUT',
            body: data,
          }
        ).then((res) => res.data)
    );
  }

  deleteSection(sectionId: number) {
    const client = useSanctumClient();

    return useAsyncData<{ success: boolean }>(
      `catalog-menu-section-delete-${sectionId}-${Date.now()}`,
      () =>
        client<{ success: boolean }>(
          `/api/admin/catalog-menus/sections/${sectionId}`,
          {
            method: 'DELETE',
          }
        ).then((res) => res)
    );
  }

  reorderSections(menuId: number, column: number, sectionIds: number[]) {
    const client = useSanctumClient();

    return useAsyncData<CatalogMenuSection[]>(
      `catalog-menu-sections-reorder-${menuId}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: CatalogMenuSection[] }>(
          `/api/admin/catalog-menus/${menuId}/sections/reorder`,
          {
            method: 'POST',
            body: { column, section_ids: sectionIds },
          }
        ).then((res) => res.data)
    );
  }

  addItem(sectionId: number, data: CatalogMenuItemFormData) {
    const client = useSanctumClient();

    return useAsyncData<CatalogMenuItem>(
      `catalog-menu-item-add-${sectionId}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: CatalogMenuItem }>(
          `/api/admin/catalog-menus/sections/${sectionId}/items`,
          {
            method: 'POST',
            body: data,
          }
        ).then((res) => res.data)
    );
  }

  updateItem(itemId: number, data: Partial<CatalogMenuItemFormData>) {
    const client = useSanctumClient();

    return useAsyncData<CatalogMenuItem>(
      `catalog-menu-item-update-${itemId}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: CatalogMenuItem }>(
          `/api/admin/catalog-menus/items/${itemId}`,
          {
            method: 'PUT',
            body: data,
          }
        ).then((res) => res.data)
    );
  }

  deleteItem(itemId: number) {
    const client = useSanctumClient();

    return useAsyncData<{ success: boolean }>(
      `catalog-menu-item-delete-${itemId}-${Date.now()}`,
      () =>
        client<{ success: boolean }>(
          `/api/admin/catalog-menus/items/${itemId}`,
          {
            method: 'DELETE',
          }
        ).then((res) => res)
    );
  }

  reorderItems(sectionId: number, itemIds: number[]) {
    const client = useSanctumClient();

    return useAsyncData<CatalogMenuItem[]>(
      `catalog-menu-items-reorder-${sectionId}-${Date.now()}`,
      () =>
        client<{ success: boolean; data: CatalogMenuItem[] }>(
          `/api/admin/catalog-menus/sections/${sectionId}/items/reorder`,
          {
            method: 'POST',
            body: { item_ids: itemIds },
          }
        ).then((res) => res.data)
    );
  }
}
