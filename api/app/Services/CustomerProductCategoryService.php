<?php

namespace App\Services\Admin;

use App\Contracts\CustomerProductCategoryServiceInterface;
use App\Contracts\ProductCategoryRepositoryInterface;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;

class CustomerProductCategoryService implements CustomerProductCategoryServiceInterface
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
}