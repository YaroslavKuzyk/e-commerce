import { defineStore } from "pinia";
import type { CatalogMenu, CatalogMenuSection } from "~/models/catalogMenu";

export const useCatalogMenuStore = defineStore("catalogMenu", () => {
  const { getMenuByCategoryId, getAllMenus, getSectionsByColumn } = useCatalogMenu();

  const menusCache = ref<Map<number, CatalogMenu | null>>(new Map());

  const fetchMenuByCategoryId = async (categoryId: number) => {
    if (menusCache.value.has(categoryId)) {
      return { data: ref(menusCache.value.get(categoryId)) };
    }

    const result = await getMenuByCategoryId(categoryId);
    if (result.data.value) {
      menusCache.value.set(categoryId, result.data.value);
    }
    return result;
  };

  const fetchAllMenus = async () => {
    const result = await getAllMenus();
    if (result.data.value) {
      // API returns object { category_id: menu } not array
      const menus = result.data.value;
      if (typeof menus === 'object' && menus !== null) {
        Object.entries(menus).forEach(([categoryId, menu]) => {
          if (menu) {
            menusCache.value.set(Number(categoryId), menu as CatalogMenu);
          }
        });
      }
    }
    return result;
  };

  const getMenuFromCache = (categoryId: number): CatalogMenu | null => {
    return menusCache.value.get(categoryId) || null;
  };

  const getSectionsForColumn = (categoryId: number, column: number): CatalogMenuSection[] => {
    const menu = menusCache.value.get(categoryId);
    return getSectionsByColumn(menu || null, column);
  };

  const clearCache = () => {
    menusCache.value.clear();
  };

  return {
    menusCache,
    fetchMenuByCategoryId,
    fetchAllMenus,
    getMenuFromCache,
    getSectionsForColumn,
    clearCache,
  };
});
