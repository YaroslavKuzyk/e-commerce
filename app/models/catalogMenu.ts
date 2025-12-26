export interface CatalogMenuItem {
  id: number;
  catalog_menu_section_id: number;
  name: string;
  link: string;
  sort_order: number;
}

export interface CatalogMenuSection {
  id: number;
  catalog_menu_id: number;
  column_index: number;
  name: string;
  link: string | null;
  icon_file_id: number | null;
  sort_order: number;
  items: CatalogMenuItem[];
}

export interface CatalogMenu {
  id: number;
  category_id: number;
  is_enabled: boolean;
  sections: CatalogMenuSection[];
}

export interface ICatalogMenuProvider {
  getMenuByCategoryId: (categoryId: number) => ReturnType<typeof useAsyncData<CatalogMenu | null>>;
  getAllMenus: () => ReturnType<typeof useAsyncData<CatalogMenu[]>>;
}
