<?php

namespace App\Services\Admin;

use App\Contracts\AdminProductServiceInterface;
use App\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductSpecification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AdminProductService implements AdminProductServiceInterface
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Get all products with optional filters and pagination.
     *
     * @param array $filters
     * @return Collection|LengthAwarePaginator
     */
    public function getAllProducts(array $filters = []): Collection|LengthAwarePaginator
    {
        if (empty($filters)) {
            return $this->productRepository->getAll();
        }

        return $this->productRepository->getAllWithFilters($filters);
    }

    /**
     * Get a product by ID.
     *
     * @param int $id
     * @return Product
     * @throws \Exception
     */
    public function getProductById(int $id): Product
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw new \Exception('Product not found');
        }

        return $product;
    }

    /**
     * Create a new product.
     *
     * @param array $data
     * @return Product
     */
    public function createProduct(array $data): Product
    {
        $product = $this->productRepository->create($data);

        // Sync attributes if provided
        if (!empty($data['attribute_ids'])) {
            $product = $this->productRepository->syncAttributes($product, $data['attribute_ids']);
        }

        // Create variants if provided
        if (!empty($data['variants']) && is_array($data['variants'])) {
            foreach ($data['variants'] as $variantData) {
                $this->productRepository->addVariant($product, $variantData);
            }
        }

        return $this->productRepository->findById($product->id);
    }

    /**
     * Update a product.
     *
     * @param int $id
     * @param array $data
     * @return Product
     * @throws \Exception
     */
    public function updateProduct(int $id, array $data): Product
    {
        $product = $this->getProductById($id);

        return $this->productRepository->update($product, $data);
    }

    /**
     * Delete a product.
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteProduct(int $id): bool
    {
        $product = $this->getProductById($id);

        return $this->productRepository->delete($product);
    }

    /**
     * Sync attributes for a product.
     *
     * @param int $productId
     * @param array $attributeIds
     * @return Product
     * @throws \Exception
     */
    public function syncAttributes(int $productId, array $attributeIds): Product
    {
        $product = $this->getProductById($productId);

        return $this->productRepository->syncAttributes($product, $attributeIds);
    }

    /**
     * Get variants for a product.
     *
     * @param int $productId
     * @return Collection
     * @throws \Exception
     */
    public function getVariants(int $productId): Collection
    {
        $product = $this->getProductById($productId);

        return $product->variants()->with(['attributeValues.attribute', 'images.file'])->get();
    }

    /**
     * Add a variant to a product.
     *
     * @param int $productId
     * @param array $data
     * @return ProductVariant
     * @throws \Exception
     */
    public function addVariant(int $productId, array $data): ProductVariant
    {
        $product = $this->getProductById($productId);

        return $this->productRepository->addVariant($product, $data);
    }

    /**
     * Update a variant.
     *
     * @param int $productId
     * @param int $variantId
     * @param array $data
     * @return ProductVariant
     * @throws \Exception
     */
    public function updateVariant(int $productId, int $variantId, array $data): ProductVariant
    {
        $product = $this->getProductById($productId);

        $variant = $product->variants()->find($variantId);

        if (!$variant) {
            throw new \Exception('Variant not found');
        }

        return $this->productRepository->updateVariant($variant, $data);
    }

    /**
     * Delete a variant.
     *
     * @param int $productId
     * @param int $variantId
     * @return bool
     * @throws \Exception
     */
    public function deleteVariant(int $productId, int $variantId): bool
    {
        $product = $this->getProductById($productId);

        $variant = $product->variants()->find($variantId);

        if (!$variant) {
            throw new \Exception('Variant not found');
        }

        return $this->productRepository->deleteVariant($variant);
    }

    /**
     * Get specifications for a product.
     *
     * @param int $productId
     * @return Collection
     * @throws \Exception
     */
    public function getSpecifications(int $productId): Collection
    {
        $product = $this->getProductById($productId);

        return $product->specifications()->orderBy('sort_order')->get();
    }

    /**
     * Add a specification to a product.
     *
     * @param int $productId
     * @param array $data
     * @return ProductSpecification
     * @throws \Exception
     */
    public function addSpecification(int $productId, array $data): ProductSpecification
    {
        $product = $this->getProductById($productId);

        $maxSortOrder = $product->specifications()->max('sort_order') ?? -1;

        return $product->specifications()->create([
            'name' => $data['name'],
            'value' => $data['value'],
            'sort_order' => $data['sort_order'] ?? ($maxSortOrder + 1),
        ]);
    }

    /**
     * Update a specification.
     *
     * @param int $productId
     * @param int $specificationId
     * @param array $data
     * @return ProductSpecification
     * @throws \Exception
     */
    public function updateSpecification(int $productId, int $specificationId, array $data): ProductSpecification
    {
        $product = $this->getProductById($productId);

        $specification = $product->specifications()->find($specificationId);

        if (!$specification) {
            throw new \Exception('Specification not found');
        }

        $specification->update($data);

        return $specification->fresh();
    }

    /**
     * Delete a specification.
     *
     * @param int $productId
     * @param int $specificationId
     * @return bool
     * @throws \Exception
     */
    public function deleteSpecification(int $productId, int $specificationId): bool
    {
        $product = $this->getProductById($productId);

        $specification = $product->specifications()->find($specificationId);

        if (!$specification) {
            throw new \Exception('Specification not found');
        }

        return $specification->delete();
    }

    /**
     * Reorder specifications.
     *
     * @param int $productId
     * @param array $specificationIds
     * @return Collection
     * @throws \Exception
     */
    public function reorderSpecifications(int $productId, array $specificationIds): Collection
    {
        $product = $this->getProductById($productId);

        foreach ($specificationIds as $index => $specificationId) {
            $product->specifications()
                ->where('id', $specificationId)
                ->update(['sort_order' => $index]);
        }

        return $product->specifications()->orderBy('sort_order')->get();
    }
}
