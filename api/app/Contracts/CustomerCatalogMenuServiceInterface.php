<?php

namespace App\Contracts;

use App\Models\CatalogMenu;
use Illuminate\Database\Eloquent\Collection;

interface CustomerCatalogMenuServiceInterface
{
    /**
     * Get a catalog menu by category ID (only if enabled).
     */
    public function getMenuByCategoryId(int $categoryId): ?CatalogMenu;

    /**
     * Get all enabled menus for root categories.
     */
    public function getAllMenus(): Collection;
}
