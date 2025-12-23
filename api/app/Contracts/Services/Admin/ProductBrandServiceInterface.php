<?php

namespace App\Contracts\Services\Admin;

use App\Models\ProductBrand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductBrandServiceInterface
{
    public function getAllBrands(array $filters = []): Collection|LengthAwarePaginator;

    public function getBrandById(int $id): ProductBrand;

    public function createBrand(array $data): ProductBrand;

    public function updateBrand(int $id, array $data): ProductBrand;

    public function deleteBrand(int $id): bool;
}
