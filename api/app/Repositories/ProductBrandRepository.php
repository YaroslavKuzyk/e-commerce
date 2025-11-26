<?php

namespace App\Repositories;

use App\Contracts\ProductBrandRepositoryInterface;
use App\Models\ProductBrand;
use Illuminate\Database\Eloquent\Collection;

class ProductBrandRepository implements ProductBrandRepositoryInterface
{
    /**
     * Get all brands.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return ProductBrand::orderBy('name')->get();
    }

    /**
     * Find a brand by ID.
     *
     * @param int $id
     * @return ProductBrand|null
     */
    public function findById(int $id): ?ProductBrand
    {
        return ProductBrand::find($id);
    }

    /**
     * Find a brand by slug.
     *
     * @param string $slug
     * @return ProductBrand|null
     */
    public function findBySlug(string $slug): ?ProductBrand
    {
        return ProductBrand::where('slug', $slug)->first();
    }

    /**
     * Create a new brand.
     *
     * @param array $data
     * @return ProductBrand
     */
    public function create(array $data): ProductBrand
    {
        return ProductBrand::create($data);
    }

    /**
     * Update a brand.
     *
     * @param ProductBrand $brand
     * @param array $data
     * @return ProductBrand
     */
    public function update(ProductBrand $brand, array $data): ProductBrand
    {
        $brand->update($data);
        return $brand->fresh();
    }

    /**
     * Delete a brand.
     *
     * @param ProductBrand $brand
     * @return bool
     */
    public function delete(ProductBrand $brand): bool
    {
        return $brand->delete();
    }
}
