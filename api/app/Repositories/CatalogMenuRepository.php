<?php

namespace App\Repositories;

use App\Contracts\CatalogMenuRepositoryInterface;
use App\Models\CatalogMenu;
use App\Models\CatalogMenuItem;
use App\Models\CatalogMenuSection;
use Illuminate\Database\Eloquent\Collection;

class CatalogMenuRepository implements CatalogMenuRepositoryInterface
{
    /**
     * Find a catalog menu by category ID.
     */
    public function findByCategoryId(int $categoryId): ?CatalogMenu
    {
        return CatalogMenu::with(['sections.items', 'sections.iconFile'])
            ->where('category_id', $categoryId)
            ->first();
    }

    /**
     * Find a catalog menu by ID.
     */
    public function findById(int $id): ?CatalogMenu
    {
        return CatalogMenu::with(['sections.items', 'sections.iconFile'])
            ->find($id);
    }

    /**
     * Create a new catalog menu.
     */
    public function create(array $data): CatalogMenu
    {
        $menu = CatalogMenu::create($data);
        return $menu->load(['sections.items', 'sections.iconFile']);
    }

    /**
     * Update a catalog menu.
     */
    public function update(CatalogMenu $menu, array $data): CatalogMenu
    {
        $menu->update($data);
        return $menu->fresh(['sections.items', 'sections.iconFile']);
    }

    /**
     * Delete a catalog menu.
     */
    public function delete(CatalogMenu $menu): bool
    {
        return $menu->delete();
    }

    /**
     * Get all enabled menus for root categories.
     */
    public function getAllEnabledForRootCategories(): Collection
    {
        return CatalogMenu::with(['sections.items', 'sections.iconFile', 'category'])
            ->enabled()
            ->whereHas('category', function ($query) {
                $query->whereNull('parent_id');
            })
            ->get();
    }

    /**
     * Find a section by ID.
     */
    public function findSectionById(int $id): ?CatalogMenuSection
    {
        return CatalogMenuSection::with(['items', 'iconFile'])->find($id);
    }

    /**
     * Create a new section.
     */
    public function createSection(array $data): CatalogMenuSection
    {
        // Set sort_order to be last in the column if not provided
        if (!isset($data['sort_order'])) {
            $maxSortOrder = CatalogMenuSection::where('catalog_menu_id', $data['catalog_menu_id'])
                ->where('column_index', $data['column_index'])
                ->max('sort_order');
            $data['sort_order'] = ($maxSortOrder ?? -1) + 1;
        }

        $section = CatalogMenuSection::create($data);
        return $section->load(['items', 'iconFile']);
    }

    /**
     * Update a section.
     */
    public function updateSection(CatalogMenuSection $section, array $data): CatalogMenuSection
    {
        $section->update($data);
        return $section->fresh(['items', 'iconFile']);
    }

    /**
     * Delete a section.
     */
    public function deleteSection(CatalogMenuSection $section): bool
    {
        return $section->delete();
    }

    /**
     * Reorder sections within a column.
     */
    public function reorderSections(int $menuId, int $column, array $sectionIds): Collection
    {
        foreach ($sectionIds as $index => $sectionId) {
            CatalogMenuSection::where('id', $sectionId)
                ->where('catalog_menu_id', $menuId)
                ->where('column_index', $column)
                ->update(['sort_order' => $index]);
        }

        return CatalogMenuSection::with(['items', 'iconFile'])
            ->where('catalog_menu_id', $menuId)
            ->where('column_index', $column)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Find an item by ID.
     */
    public function findItemById(int $id): ?CatalogMenuItem
    {
        return CatalogMenuItem::find($id);
    }

    /**
     * Create a new item.
     */
    public function createItem(array $data): CatalogMenuItem
    {
        // Set sort_order to be last if not provided
        if (!isset($data['sort_order'])) {
            $maxSortOrder = CatalogMenuItem::where('catalog_menu_section_id', $data['catalog_menu_section_id'])
                ->max('sort_order');
            $data['sort_order'] = ($maxSortOrder ?? -1) + 1;
        }

        return CatalogMenuItem::create($data);
    }

    /**
     * Update an item.
     */
    public function updateItem(CatalogMenuItem $item, array $data): CatalogMenuItem
    {
        $item->update($data);
        return $item->fresh();
    }

    /**
     * Delete an item.
     */
    public function deleteItem(CatalogMenuItem $item): bool
    {
        return $item->delete();
    }

    /**
     * Reorder items within a section.
     */
    public function reorderItems(int $sectionId, array $itemIds): Collection
    {
        foreach ($itemIds as $index => $itemId) {
            CatalogMenuItem::where('id', $itemId)
                ->where('catalog_menu_section_id', $sectionId)
                ->update(['sort_order' => $index]);
        }

        return CatalogMenuItem::where('catalog_menu_section_id', $sectionId)
            ->orderBy('sort_order')
            ->get();
    }
}
