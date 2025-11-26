<?php

namespace App\Repositories;

use App\Contracts\ProductBrandRepositoryInterface;
use App\Models\ProductBrand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
     * Get all brands with filters and optional pagination.
     *
     * @param array $filters
     * @return Collection|LengthAwarePaginator
     */
    public function getAllWithFilters(array $filters): Collection|LengthAwarePaginator
    {
        $query = ProductBrand::query();

        if (!empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }

        if (!empty($filters['slug'])) {
            $query->where('slug', 'like', "%{$filters['slug']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->orderBy('name');

        if (!empty($filters['per_page'])) {
            return $query->paginate(
                (int) $filters['per_page'],
                ['*'],
                'page',
                (int) ($filters['page'] ?? 1)
            );
        }

        return $query->get();
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
