<?php

namespace App\Repositories;

use App\Contracts\ProductCategoryRepositoryInterface;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;

class ProductCategoryRepository implements ProductCategoryRepositoryInterface
{
    /**
     * Get all categories with tree structure.
     *
     * @return Collection
     */
    public function getAllWithTree(): Collection
    {
        return ProductCategory::with(['subcategories' => function ($query) {
            $this->loadSubcategoriesRecursively($query);
        }])
            ->rootCategories()
            ->withCount('subcategories')
            ->orderBy('name')
            ->get();
    }

    /**
     * Recursively load subcategories.
     *
     * @param $query
     * @return void
     */
    protected function loadSubcategoriesRecursively($query): void
    {
        $query->withCount('subcategories')
            ->with(['subcategories' => function ($q) {
                $this->loadSubcategoriesRecursively($q);
            }]);
    }

    /**
     * Get all root categories (parent_id = null).
     *
     * @return Collection
     */
    public function getRootCategories(): Collection
    {
        return ProductCategory::rootCategories()
            ->withCount('subcategories')
            ->orderBy('name')
            ->get();
    }

    /**
     * Find a category by ID.
     *
     * @param int $id
     * @return ProductCategory|null
     */
    public function findById(int $id): ?ProductCategory
    {
        return ProductCategory::with(['subcategories' => function ($query) {
            $query->withCount('subcategories');
        }])
            ->withCount('subcategories')
            ->find($id);
    }

    /**
     * Find a category by slug.
     *
     * @param string $slug
     * @return ProductCategory|null
     */
    public function findBySlug(string $slug): ?ProductCategory
    {
        return ProductCategory::where('slug', $slug)
            ->withCount('subcategories')
            ->first();
    }

    /**
     * Create a new category.
     *
     * @param array $data
     * @return ProductCategory
     */
    public function create(array $data): ProductCategory
    {
        $category = ProductCategory::create($data);
        return $category->load(['subcategories'])->loadCount('subcategories');
    }

    /**
     * Update a category.
     *
     * @param ProductCategory $category
     * @param array $data
     * @return ProductCategory
     */
    public function update(ProductCategory $category, array $data): ProductCategory
    {
        $category->update($data);
        return $category->fresh(['subcategories'])->loadCount('subcategories');
    }

    /**
     * Delete a category and all its subcategories recursively.
     *
     * @param ProductCategory $category
     * @return bool
     */
    public function delete(ProductCategory $category): bool
    {
        // Recursively delete all subcategories first
        $this->deleteSubcategoriesRecursively($category);

        // Delete the category itself
        return $category->delete();
    }

    /**
     * Recursively delete all subcategories.
     *
     * @param ProductCategory $category
     * @return void
     */
    protected function deleteSubcategoriesRecursively(ProductCategory $category): void
    {
        $subcategories = $category->subcategories;

        foreach ($subcategories as $subcategory) {
            // Recursively delete children first
            $this->deleteSubcategoriesRecursively($subcategory);

            // Delete the subcategory
            $subcategory->delete();
        }
    }

    /**
     * Get subcategories count for a category.
     *
     * @param ProductCategory $category
     * @return int
     */
    public function getSubcategoriesCount(ProductCategory $category): int
    {
        return $category->subcategories()->count();
    }
}
