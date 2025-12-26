<?php

namespace App\Contracts;

use App\Models\CatalogMenu;
use App\Models\CatalogMenuItem;
use App\Models\CatalogMenuSection;
use Illuminate\Database\Eloquent\Collection;

interface AdminCatalogMenuServiceInterface
{
    /**
     * Get a catalog menu by category ID.
     */
    public function getMenuByCategoryId(int $categoryId): ?CatalogMenu;

    /**
     * Create or update a catalog menu for a category.
     */
    public function createOrUpdateMenu(int $categoryId, array $data): CatalogMenu;

    /**
     * Delete a catalog menu.
     */
    public function deleteMenu(int $categoryId): bool;

    /**
     * Add a section to a menu.
     */
    public function addSection(int $menuId, array $data): CatalogMenuSection;

    /**
     * Update a section.
     */
    public function updateSection(int $sectionId, array $data): CatalogMenuSection;

    /**
     * Delete a section.
     */
    public function deleteSection(int $sectionId): bool;

    /**
     * Reorder sections within a column.
     */
    public function reorderSections(int $menuId, int $column, array $sectionIds): Collection;

    /**
     * Add an item to a section.
     */
    public function addItem(int $sectionId, array $data): CatalogMenuItem;

    /**
     * Update an item.
     */
    public function updateItem(int $itemId, array $data): CatalogMenuItem;

    /**
     * Delete an item.
     */
    public function deleteItem(int $itemId): bool;

    /**
     * Reorder items within a section.
     */
    public function reorderItems(int $sectionId, array $itemIds): Collection;
}
