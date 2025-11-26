<?php

namespace App\Contracts;

use App\Models\ProductBrand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductBrandRepositoryInterface
{
    public function getAll(): Collection;

    public function getAllWithFilters(array $filters): Collection|LengthAwarePaginator;

    public function findById(int $id): ?ProductBrand;

    public function findBySlug(string $slug): ?ProductBrand;

    public function create(array $data): ProductBrand;

    public function update(ProductBrand $brand, array $data): ProductBrand;

    public function delete(ProductBrand $brand): bool;
}
