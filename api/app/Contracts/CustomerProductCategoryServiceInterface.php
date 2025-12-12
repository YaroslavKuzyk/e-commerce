<?php

namespace App\Contracts;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;

interface CustomerProductCategoryServiceInterface
{
    /**
     * Get all categories with tree structure.
     *
     * @return Collection
     */
    public function getAllCategories(): Collection;

    /**
     * Get a category by ID.
     *
     * @param int $id
     * @return ProductCategory
     */
    public function getCategoryById(int $id): ProductCategory;
}
