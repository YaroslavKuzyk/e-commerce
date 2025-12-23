<?php

namespace App\Services;

use App\Contracts\Services\Customer\ProductCategoryServiceInterface;
use App\Contracts\Repositories\ProductCategoryRepositoryInterface;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerProductCategoryService implements ProductCategoryServiceInterface
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
     * Get paginated flat list of categories.
     *
     * @param int $page
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getCategoriesPaginated(int $page = 1, int $limit = 15): LengthAwarePaginator
    {
        return $this->productCategoryRepository->getPaginatedFlat($page, $limit);
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