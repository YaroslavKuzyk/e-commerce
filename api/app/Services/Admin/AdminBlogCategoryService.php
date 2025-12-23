<?php

namespace App\Services\Admin;

use App\Contracts\Services\Admin\BlogCategoryServiceInterface;
use App\Models\BlogCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AdminBlogCategoryService implements BlogCategoryServiceInterface
{
    /**
     * Get all blog categories with optional filters and pagination.
     *
     * @param array $filters
     * @return Collection|LengthAwarePaginator
     */
    public function getAllCategories(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = BlogCategory::query()->withCount('posts');

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->orderBy('sort_order');

        if (!empty($filters['per_page'])) {
            return $query->paginate($filters['per_page']);
        }

        return $query->get();
    }

    /**
     * Get a blog category by ID.
     *
     * @param int $id
     * @return BlogCategory
     * @throws \Exception
     */
    public function getCategoryById(int $id): BlogCategory
    {
        $category = BlogCategory::withCount('posts')->find($id);

        if (!$category) {
            throw new \Exception('Blog category not found');
        }

        return $category;
    }

    /**
     * Create a new blog category.
     *
     * @param array $data
     * @return BlogCategory
     */
    public function createCategory(array $data): BlogCategory
    {
        return BlogCategory::create($data);
    }

    /**
     * Update a blog category.
     *
     * @param int $id
     * @param array $data
     * @return BlogCategory
     * @throws \Exception
     */
    public function updateCategory(int $id, array $data): BlogCategory
    {
        $category = $this->getCategoryById($id);
        $category->update($data);

        return $category->fresh();
    }

    /**
     * Delete a blog category.
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteCategory(int $id): bool
    {
        $category = $this->getCategoryById($id);

        if ($category->posts()->count() > 0) {
            throw new \Exception('Cannot delete category with posts');
        }

        return $category->delete();
    }

    /**
     * Reorder blog categories.
     *
     * @param array $categoryIds
     * @return Collection
     */
    public function reorderCategories(array $categoryIds): Collection
    {
        foreach ($categoryIds as $index => $categoryId) {
            BlogCategory::where('id', $categoryId)->update(['sort_order' => $index]);
        }

        return BlogCategory::whereIn('id', $categoryIds)->orderBy('sort_order')->get();
    }
}
