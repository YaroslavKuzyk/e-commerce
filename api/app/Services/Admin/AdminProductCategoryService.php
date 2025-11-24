<?php

namespace App\Services\Admin;

use App\Contracts\AdminProductCategoryServiceInterface;
use App\Contracts\ProductCategoryRepositoryInterface;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class AdminProductCategoryService implements AdminProductCategoryServiceInterface
{
    public function __construct(
        protected ProductCategoryRepositoryInterface $productCategoryRepository
    ) {}

    /**
     * Get all categories with tree structure.
     *
     * @return Collection
     */
    public function getAllCategories(): Collection
    {
        return $this->productCategoryRepository->getAllWithTree();
    }

    /**
     * Get a category by ID.
     *
     * @param int $id
     * @return ProductCategory
     * @throws \Exception
     */
    public function getCategoryById(int $id): ProductCategory
    {
        $category = $this->productCategoryRepository->findById($id);

        if (!$category) {
            throw new \Exception('Category not found');
        }

        return $category;
    }

    /**
     * Create a new category.
     *
     * @param array $data
     * @return ProductCategory
     */
    public function createCategory(array $data): ProductCategory
    {
        // Handle logo upload
        if (isset($data['logo']) && $data['logo']) {
            $data['logo_path'] = $data['logo']->store('categories/logos', 'public');
            unset($data['logo']);
        }

        // Handle menu image upload
        if (isset($data['menu_image']) && $data['menu_image']) {
            $data['menu_image_path'] = $data['menu_image']->store('categories/menu-images', 'public');
            unset($data['menu_image']);
        }

        return $this->productCategoryRepository->create($data);
    }

    /**
     * Update a category.
     *
     * @param int $id
     * @param array $data
     * @return ProductCategory
     * @throws \Exception
     */
    public function updateCategory(int $id, array $data): ProductCategory
    {
        $category = $this->getCategoryById($id);

        // Handle logo upload
        if (isset($data['logo']) && $data['logo']) {
            // Delete old logo if exists
            if ($category->logo_path && Storage::disk('public')->exists($category->logo_path)) {
                Storage::disk('public')->delete($category->logo_path);
            }
            $data['logo_path'] = $data['logo']->store('categories/logos', 'public');
            unset($data['logo']);
        }

        // Handle menu image upload
        if (isset($data['menu_image']) && $data['menu_image']) {
            // Delete old menu image if exists
            if ($category->menu_image_path && Storage::disk('public')->exists($category->menu_image_path)) {
                Storage::disk('public')->delete($category->menu_image_path);
            }
            $data['menu_image_path'] = $data['menu_image']->store('categories/menu-images', 'public');
            unset($data['menu_image']);
        }

        return $this->productCategoryRepository->update($category, $data);
    }

    /**
     * Delete a category.
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteCategory(int $id): bool
    {
        $category = $this->getCategoryById($id);

        // Delete associated files
        if ($category->logo_path && Storage::disk('public')->exists($category->logo_path)) {
            Storage::disk('public')->delete($category->logo_path);
        }

        if ($category->menu_image_path && Storage::disk('public')->exists($category->menu_image_path)) {
            Storage::disk('public')->delete($category->menu_image_path);
        }

        return $this->productCategoryRepository->delete($category);
    }
}
