<?php

namespace App\Contracts;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;

interface ProductCategoryRepositoryInterface
{
    /**
     * Get all categories with tree structure.
     *
     * @return Collection
     */
    public function getAllWithTree(): Collection;

    /**
     * Get all root categories (parent_id = null).
     *
     * @return Collection
     */
    public function getRootCategories(): Collection;

    /**
     * Find a category by ID.
     *
     * @param int $id
     * @return ProductCategory|null
     */
    public function findById(int $id): ?ProductCategory;

    /**
     * Find a category by slug.
     *
     * @param string $slug
     * @return ProductCategory|null
     */
    public function findBySlug(string $slug): ?ProductCategory;

    /**
     * Create a new category.
     *
     * @param array $data
     * @return ProductCategory
     */
    public function create(array $data): ProductCategory;

    /**
     * Update a category.
     *
     * @param ProductCategory $category
     * @param array $data
     * @return ProductCategory
     */
    public function update(ProductCategory $category, array $data): ProductCategory;

    /**
     * Delete a category.
     *
     * @param ProductCategory $category
     * @return bool
     */
    public function delete(ProductCategory $category): bool;

    /**
     * Get subcategories count for a category.
     *
     * @param ProductCategory $category
     * @return int
     */
    public function getSubcategoriesCount(ProductCategory $category): int;
}
