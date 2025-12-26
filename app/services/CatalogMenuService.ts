import type { CatalogMenu, ICatalogMenuProvider } from "~/models/catalogMenu";

export class CatalogMenuService implements ICatalogMenuProvider {
  getMenuByCategoryId(categoryId: number) {
    const client = useSanctumClient();

    return useAsyncData<CatalogMenu | null>(
      `catalog-menu-${categoryId}`,
      () =>
        client<{ success: boolean; data: CatalogMenu | null }>(
          `/api/catalog-menus/category/${categoryId}`
        ).then((res) => res.data)
    );
  }

  getAllMenus() {
    const client = useSanctumClient();

    return useAsyncData<CatalogMenu[]>("catalog-menus", () =>
      client<{ success: boolean; data: CatalogMenu[] }>(
        `/api/catalog-menus`
      ).then((res) => res.data)
    );
  }
}
