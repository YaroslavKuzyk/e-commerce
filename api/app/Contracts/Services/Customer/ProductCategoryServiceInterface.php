<?php

namespace App\Contracts\Services\Customer;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductCategoryServiceInterface
{
    /**
     * Get all categories with tree structure.
     *
     * @return Collection
     */
    public function getAllCategories(): Collection;

    /**
     * Get paginated flat list of categories.
     *
     * @param int $page
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getCategoriesPaginated(int $page = 1, int $limit = 15): LengthAwarePaginator;

    /**
     * Get a category by ID.
     *
     * @param int $id
     * @return ProductCategory
     */
    public function getCategoryById(int $id): ProductCategory;
}
