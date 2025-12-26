<?php

namespace App\Contracts;

use App\Models\CatalogMenu;
use App\Models\CatalogMenuItem;
use App\Models\CatalogMenuSection;
use Illuminate\Database\Eloquent\Collection;

interface CatalogMenuRepositoryInterface
{
    /**
     * Find a catalog menu by category ID.
     */
    public function findByCategoryId(int $categoryId): ?CatalogMenu;

    /**
     * Find a catalog menu by ID.
     */
    public function findById(int $id): ?CatalogMenu;

    /**
     * Create a new catalog menu.
     */
    public function create(array $data): CatalogMenu;

    /**
     * Update a catalog menu.
     */
    public function update(CatalogMenu $menu, array $data): CatalogMenu;

    /**
     * Delete a catalog menu.
     */
    public function delete(CatalogMenu $menu): bool;

    /**
     * Get all enabled menus for root categories.
     */
    public function getAllEnabledForRootCategories(): Collection;

    /**
     * Find a section by ID.
     */
    public function findSectionById(int $id): ?CatalogMenuSection;

    /**
     * Create a new section.
     */
    public function createSection(array $data): CatalogMenuSection;

    /**
     * Update a section.
     */
    public function updateSection(CatalogMenuSection $section, array $data): CatalogMenuSection;

    /**
     * Delete a section.
     */
    public function deleteSection(CatalogMenuSection $section): bool;

    /**
     * Reorder sections within a column.
     */
    public function reorderSections(int $menuId, int $column, array $sectionIds): Collection;

    /**
     * Find an item by ID.
     */
    public function findItemById(int $id): ?CatalogMenuItem;

    /**
     * Create a new item.
     */
    public function createItem(array $data): CatalogMenuItem;

    /**
     * Update an item.
     */
    public function updateItem(CatalogMenuItem $item, array $data): CatalogMenuItem;

    /**
     * Delete an item.
     */
    public function deleteItem(CatalogMenuItem $item): bool;

    /**
     * Reorder items within a section.
     */
    public function reorderItems(int $sectionId, array $itemIds): Collection;
}
