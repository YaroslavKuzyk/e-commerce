<?php

namespace App\Services;

use App\Contracts\CustomerCatalogMenuServiceInterface;
use App\Contracts\CatalogMenuRepositoryInterface;
use App\Models\CatalogMenu;
use Illuminate\Database\Eloquent\Collection;

class CustomerCatalogMenuService implements CustomerCatalogMenuServiceInterface
{
    public function __construct(
        protected CatalogMenuRepositoryInterface $catalogMenuRepository
    ) {}

    /**
     * Get a catalog menu by category ID (only if enabled).
     */
    public function getMenuByCategoryId(int $categoryId): ?CatalogMenu
    {
        $menu = $this->catalogMenuRepository->findByCategoryId($categoryId);

        if ($menu && !$menu->is_enabled) {
            return null;
        }

        return $menu;
    }

    /**
     * Get all enabled menus for root categories.
     */
    public function getAllMenus(): Collection
    {
        return $this->catalogMenuRepository->getAllEnabledForRootCategories();
    }
}
