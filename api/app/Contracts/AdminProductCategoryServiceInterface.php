<?php

namespace App\Contracts;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;

interface AdminProductCategoryServiceInterface
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

    /**
     * Create a new category.
     *
     * @param array $data
     * @return ProductCategory
     */
    public function createCategory(array $data): ProductCategory;

    /**
     * Update a category.
     *
     * @param int $id
     * @param array $data
     * @return ProductCategory
     */
    public function updateCategory(int $id, array $data): ProductCategory;

    /**
     * Delete a category.
     *
     * @param int $id
     * @return bool
     */
    public function deleteCategory(int $id): bool;
}
