import type { CatalogMenu, CatalogMenuSection } from "~/models/catalogMenu";

export const useCatalogMenu = () => {
  const client = useSanctumClient();

  const getMenuByCategoryId = (categoryId: number) => {
    return useAsyncData<CatalogMenu | null>(
      `catalog-menu-${categoryId}`,
      () =>
        client<{ success: boolean; data: CatalogMenu | null }>(
          `/api/catalog-menus/category/${categoryId}`
        ).then((res) => res.data)
    );
  };

  const getAllMenus = () => {
    return useAsyncData<Record<number, CatalogMenu>>("catalog-menus", () =>
      client<{ success: boolean; data: Record<number, CatalogMenu> }>(
        `/api/catalog-menus`
      ).then((res) => res.data)
    );
  };

  const getSectionsByColumn = (menu: CatalogMenu | null, column: number): CatalogMenuSection[] => {
    if (!menu || !menu.sections) return [];
    return menu.sections
      .filter((s) => s.column_index === column)
      .sort((a, b) => a.sort_order - b.sort_order);
  };

  return {
    getMenuByCategoryId,
    getAllMenus,
    getSectionsByColumn,
  };
};
