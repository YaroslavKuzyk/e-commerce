<?php

namespace App\Repositories;

use App\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Get all products.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Product::with(['category', 'brand', 'attributes.values', 'variants.attributeValues'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all products with filters and optional pagination.
     *
     * @param array $filters
     * @return Collection|LengthAwarePaginator
     */
    public function getAllWithFilters(array $filters): Collection|LengthAwarePaginator
    {
        $query = Product::with(['category', 'brand', 'mainImage']);

        if (!empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }

        if (!empty($filters['slug'])) {
            $query->where('slug', 'like', "%{$filters['slug']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
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
     * Find a product by ID.
     *
     * @param int $id
     * @return Product|null
     */
    public function findById(int $id): ?Product
    {
        return Product::with([
            'category',
            'brand',
            'mainImage',
            'attributes.values',
            'variants.attributeValues.attribute',
            'variants.images.file',
            'specifications'
        ])->find($id);
    }

    /**
     * Find a product by slug.
     *
     * @param string $slug
     * @return Product|null
     */
    public function findBySlug(string $slug): ?Product
    {
        return Product::with([
            'category',
            'brand',
            'mainImage',
            'attributes.values',
            'variants.attributeValues.attribute',
            'variants.images.file',
            'specifications'
        ])->where('slug', $slug)->first();
    }

    /**
     * Create a new product.
     *
     * @param array $data
     * @return Product
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update a product.
     *
     * @param Product $product
     * @param array $data
     * @return Product
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh()->load([
            'category',
            'brand',
            'mainImage',
            'attributes.values',
            'variants.attributeValues.attribute',
            'variants.images.file',
            'specifications'
        ]);
    }

    /**
     * Delete a product.
     *
     * @param Product $product
     * @return bool
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Sync attributes for a product.
     *
     * @param Product $product
     * @param array $attributeIds
     * @return Product
     */
    public function syncAttributes(Product $product, array $attributeIds): Product
    {
        $product->attributes()->sync($attributeIds);
        return $product->fresh()->load('attributes.values');
    }

    /**
     * Add a variant to a product.
     *
     * @param Product $product
     * @param array $data
     * @return ProductVariant
     */
    public function addVariant(Product $product, array $data): ProductVariant
    {
        $variant = $product->variants()->create($data);

        if (!empty($data['attribute_value_ids'])) {
            $variant->attributeValues()->sync($data['attribute_value_ids']);
        }

        if (!empty($data['images'])) {
            foreach ($data['images'] as $index => $imageData) {
                $variant->images()->create([
                    'file_id' => $imageData['file_id'],
                    'sort_order' => $imageData['sort_order'] ?? $index,
                    'is_primary' => $imageData['is_primary'] ?? ($index === 0),
                ]);
            }
        }

        return $variant->fresh()->load(['attributeValues.attribute', 'images.file']);
    }

    /**
     * Update a variant.
     *
     * @param ProductVariant $variant
     * @param array $data
     * @return ProductVariant
     */
    public function updateVariant(ProductVariant $variant, array $data): ProductVariant
    {
        $variant->update($data);

        if (isset($data['attribute_value_ids'])) {
            $variant->attributeValues()->sync($data['attribute_value_ids']);
        }

        if (isset($data['images'])) {
            $variant->images()->delete();
            foreach ($data['images'] as $index => $imageData) {
                $variant->images()->create([
                    'file_id' => $imageData['file_id'],
                    'sort_order' => $imageData['sort_order'] ?? $index,
                    'is_primary' => $imageData['is_primary'] ?? ($index === 0),
                ]);
            }
        }

        return $variant->fresh()->load(['attributeValues.attribute', 'images.file']);
    }

    /**
     * Delete a variant.
     *
     * @param ProductVariant $variant
     * @return bool
     */
    public function deleteVariant(ProductVariant $variant): bool
    {
        return $variant->delete();
    }

    /**
     * Find a variant by ID.
     *
     * @param int $id
     * @return ProductVariant|null
     */
    public function findVariantById(int $id): ?ProductVariant
    {
        return ProductVariant::with(['attributeValues.attribute', 'images.file'])->find($id);
    }
}
