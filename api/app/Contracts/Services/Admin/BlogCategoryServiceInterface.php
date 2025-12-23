<?php

namespace App\Contracts\Services\Admin;

use App\Models\BlogCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface BlogCategoryServiceInterface
{
    /**
     * Get all blog categories with optional filters and pagination.
     *
     * @param array $filters
     * @return Collection|LengthAwarePaginator
     */
    public function getAllCategories(array $filters = []): Collection|LengthAwarePaginator;

    /**
     * Get a blog category by ID.
     *
     * @param int $id
     * @return BlogCategory
     */
    public function getCategoryById(int $id): BlogCategory;

    /**
     * Create a new blog category.
     *
     * @param array $data
     * @return BlogCategory
     */
    public function createCategory(array $data): BlogCategory;

    /**
     * Update a blog category.
     *
     * @param int $id
     * @param array $data
     * @return BlogCategory
     */
    public function updateCategory(int $id, array $data): BlogCategory;

    /**
     * Delete a blog category.
     *
     * @param int $id
     * @return bool
     */
    public function deleteCategory(int $id): bool;

    /**
     * Reorder blog categories.
     *
     * @param array $categoryIds
     * @return Collection
     */
    public function reorderCategories(array $categoryIds): Collection;
}
