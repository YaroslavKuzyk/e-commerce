<?php

namespace App\Services\Admin;

use App\Contracts\AdminCatalogMenuServiceInterface;
use App\Contracts\CatalogMenuRepositoryInterface;
use App\Models\CatalogMenu;
use App\Models\CatalogMenuItem;
use App\Models\CatalogMenuSection;
use Illuminate\Database\Eloquent\Collection;

class AdminCatalogMenuService implements AdminCatalogMenuServiceInterface
{
    public function __construct(
        protected CatalogMenuRepositoryInterface $catalogMenuRepository
    ) {}

    /**
     * Get a catalog menu by category ID.
     */
    public function getMenuByCategoryId(int $categoryId): ?CatalogMenu
    {
        return $this->catalogMenuRepository->findByCategoryId($categoryId);
    }

    /**
     * Create or update a catalog menu for a category.
     */
    public function createOrUpdateMenu(int $categoryId, array $data): CatalogMenu
    {
        $menu = $this->catalogMenuRepository->findByCategoryId($categoryId);

        if ($menu) {
            return $this->catalogMenuRepository->update($menu, $data);
        }

        $data['category_id'] = $categoryId;
        return $this->catalogMenuRepository->create($data);
    }

    /**
     * Delete a catalog menu.
     */
    public function deleteMenu(int $categoryId): bool
    {
        $menu = $this->catalogMenuRepository->findByCategoryId($categoryId);

        if (!$menu) {
            throw new \Exception('Catalog menu not found');
        }

        return $this->catalogMenuRepository->delete($menu);
    }

    /**
     * Add a section to a menu.
     */
    public function addSection(int $menuId, array $data): CatalogMenuSection
    {
        $menu = $this->catalogMenuRepository->findById($menuId);

        if (!$menu) {
            throw new \Exception('Catalog menu not found');
        }

        $data['catalog_menu_id'] = $menuId;
        return $this->catalogMenuRepository->createSection($data);
    }

    /**
     * Update a section.
     */
    public function updateSection(int $sectionId, array $data): CatalogMenuSection
    {
        $section = $this->catalogMenuRepository->findSectionById($sectionId);

        if (!$section) {
            throw new \Exception('Section not found');
        }

        return $this->catalogMenuRepository->updateSection($section, $data);
    }

    /**
     * Delete a section.
     */
    public function deleteSection(int $sectionId): bool
    {
        $section = $this->catalogMenuRepository->findSectionById($sectionId);

        if (!$section) {
            throw new \Exception('Section not found');
        }

        return $this->catalogMenuRepository->deleteSection($section);
    }

    /**
     * Reorder sections within a column.
     */
    public function reorderSections(int $menuId, int $column, array $sectionIds): Collection
    {
        $menu = $this->catalogMenuRepository->findById($menuId);

        if (!$menu) {
            throw new \Exception('Catalog menu not found');
        }

        return $this->catalogMenuRepository->reorderSections($menuId, $column, $sectionIds);
    }

    /**
     * Add an item to a section.
     */
    public function addItem(int $sectionId, array $data): CatalogMenuItem
    {
        $section = $this->catalogMenuRepository->findSectionById($sectionId);

        if (!$section) {
            throw new \Exception('Section not found');
        }

        $data['catalog_menu_section_id'] = $sectionId;
        return $this->catalogMenuRepository->createItem($data);
    }

    /**
     * Update an item.
     */
    public function updateItem(int $itemId, array $data): CatalogMenuItem
    {
        $item = $this->catalogMenuRepository->findItemById($itemId);

        if (!$item) {
            throw new \Exception('Item not found');
        }

        return $this->catalogMenuRepository->updateItem($item, $data);
    }

    /**
     * Delete an item.
     */
    public function deleteItem(int $itemId): bool
    {
        $item = $this->catalogMenuRepository->findItemById($itemId);

        if (!$item) {
            throw new \Exception('Item not found');
        }

        return $this->catalogMenuRepository->deleteItem($item);
    }

    /**
     * Reorder items within a section.
     */
    public function reorderItems(int $sectionId, array $itemIds): Collection
    {
        $section = $this->catalogMenuRepository->findSectionById($sectionId);

        if (!$section) {
            throw new \Exception('Section not found');
        }

        return $this->catalogMenuRepository->reorderItems($sectionId, $itemIds);
    }
}
