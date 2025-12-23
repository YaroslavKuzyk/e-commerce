<?php

namespace App\Contracts\Repositories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function getAll(): Collection;

    public function getAllWithFilters(array $filters): Collection|LengthAwarePaginator;

    public function getAvailableFilters(array $currentFilters = []): array;

    public function findById(int $id): ?Product;

    public function findBySlug(string $slug): ?Product;

    public function create(array $data): Product;

    public function update(Product $product, array $data): Product;

    public function delete(Product $product): bool;

    public function syncAttributes(Product $product, array $attributeIds): Product;

    public function addVariant(Product $product, array $data): ProductVariant;

    public function updateVariant(ProductVariant $variant, array $data): ProductVariant;

    public function deleteVariant(ProductVariant $variant): bool;

    public function findVariantById(int $id): ?ProductVariant;
}
