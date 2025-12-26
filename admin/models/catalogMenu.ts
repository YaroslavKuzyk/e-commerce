export interface CatalogMenuItem {
  id: number;
  catalog_menu_section_id: number;
  name: string;
  link: string;
  sort_order: number;
  created_at: string;
  updated_at: string;
}

export interface CatalogMenuSection {
  id: number;
  catalog_menu_id: number;
  column_index: 1 | 2 | 3;
  name: string;
  link: string | null;
  icon_file_id: number | null;
  sort_order: number;
  items: CatalogMenuItem[];
  created_at: string;
  updated_at: string;
}

export interface CatalogMenu {
  id: number;
  category_id: number;
  is_enabled: boolean;
  sections: CatalogMenuSection[];
  created_at: string;
  updated_at: string;
}

export interface CatalogMenuSectionFormData {
  column_index: 1 | 2 | 3;
  name: string;
  link?: string | null;
  icon_file_id?: number | null;
}

export interface CatalogMenuItemFormData {
  name: string;
  link: string;
}

export interface ICatalogMenuProvider {
  getMenuByCategoryId: (categoryId: number) => ReturnType<typeof useAsyncData<CatalogMenu | null>>;
  createOrUpdateMenu: (categoryId: number, data: { is_enabled: boolean }) => ReturnType<typeof useAsyncData<CatalogMenu>>;
  deleteMenu: (categoryId: number) => ReturnType<typeof useAsyncData<{ success: boolean }>>;

  addSection: (menuId: number, data: CatalogMenuSectionFormData) => ReturnType<typeof useAsyncData<CatalogMenuSection>>;
  updateSection: (sectionId: number, data: Partial<CatalogMenuSectionFormData>) => ReturnType<typeof useAsyncData<CatalogMenuSection>>;
  deleteSection: (sectionId: number) => ReturnType<typeof useAsyncData<{ success: boolean }>>;
  reorderSections: (menuId: number, column: number, sectionIds: number[]) => ReturnType<typeof useAsyncData<CatalogMenuSection[]>>;

  addItem: (sectionId: number, data: CatalogMenuItemFormData) => ReturnType<typeof useAsyncData<CatalogMenuItem>>;
  updateItem: (itemId: number, data: Partial<CatalogMenuItemFormData>) => ReturnType<typeof useAsyncData<CatalogMenuItem>>;
  deleteItem: (itemId: number) => ReturnType<typeof useAsyncData<{ success: boolean }>>;
  reorderItems: (sectionId: number, itemIds: number[]) => ReturnType<typeof useAsyncData<CatalogMenuItem[]>>;
}
