<?php

namespace App\Services\Admin;

use App\Contracts\AdminProductBrandServiceInterface;
use App\Contracts\ProductBrandRepositoryInterface;
use App\Models\ProductBrand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AdminProductBrandService implements AdminProductBrandServiceInterface
{
    public function __construct(
        protected ProductBrandRepositoryInterface $productBrandRepository
    ) {}

    /**
     * Get all brands with optional filters and pagination.
     *
     * @param array $filters
     * @return Collection|LengthAwarePaginator
     */
    public function getAllBrands(array $filters = []): Collection|LengthAwarePaginator
    {
        if (empty($filters)) {
            return $this->productBrandRepository->getAll();
        }

        return $this->productBrandRepository->getAllWithFilters($filters);
    }

    /**
     * Get a brand by ID.
     *
     * @param int $id
     * @return ProductBrand
     * @throws \Exception
     */
    public function getBrandById(int $id): ProductBrand
    {
        $brand = $this->productBrandRepository->findById($id);

        if (!$brand) {
            throw new \Exception('Brand not found');
        }

        return $brand;
    }

    /**
     * Create a new brand.
     *
     * @param array $data
     * @return ProductBrand
     */
    public function createBrand(array $data): ProductBrand
    {
        return $this->productBrandRepository->create($data);
    }

    /**
     * Update a brand.
     *
     * @param int $id
     * @param array $data
     * @return ProductBrand
     * @throws \Exception
     */
    public function updateBrand(int $id, array $data): ProductBrand
    {
        $brand = $this->getBrandById($id);

        return $this->productBrandRepository->update($brand, $data);
    }

    /**
     * Delete a brand.
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteBrand(int $id): bool
    {
        $brand = $this->getBrandById($id);

        return $this->productBrandRepository->delete($brand);
    }
}
