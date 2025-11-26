<?php

namespace App\Contracts;

use App\Models\ProductBrand;
use Illuminate\Database\Eloquent\Collection;

interface AdminProductBrandServiceInterface
{
    public function getAllBrands(): Collection;

    public function getBrandById(int $id): ProductBrand;

    public function createBrand(array $data): ProductBrand;

    public function updateBrand(int $id, array $data): ProductBrand;

    public function deleteBrand(int $id): bool;
}
