<?php

namespace App\Contracts;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductSpecification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AdminProductServiceInterface
{
    public function getAllProducts(array $filters = []): Collection|LengthAwarePaginator;

    public function getProductById(int $id): Product;

    public function createProduct(array $data): Product;

    public function updateProduct(int $id, array $data): Product;

    public function deleteProduct(int $id): bool;

    public function syncAttributes(int $productId, array $attributeIds): Product;

    public function getVariants(int $productId): Collection;

    public function addVariant(int $productId, array $data): ProductVariant;

    public function updateVariant(int $productId, int $variantId, array $data): ProductVariant;

    public function deleteVariant(int $productId, int $variantId): bool;

    public function getSpecifications(int $productId): Collection;

    public function addSpecification(int $productId, array $data): ProductSpecification;

    public function updateSpecification(int $productId, int $specificationId, array $data): ProductSpecification;

    public function deleteSpecification(int $productId, int $specificationId): bool;

    public function reorderSpecifications(int $productId, array $specificationIds): Collection;
}
