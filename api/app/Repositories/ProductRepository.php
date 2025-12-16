<?php

namespace App\Repositories;

use App\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductBrand;
use App\Models\Attribute;
use App\Models\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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
        $query = Product::with(['category.parent', 'brand', 'mainImage', 'variants.attributeValues']);

        // Search by name, slug, or description
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }

        if (!empty($filters['slug'])) {
            $query->where('slug', 'like', "%{$filters['slug']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Category filter with subcategories support
        if (!empty($filters['category_id'])) {
            $categoryIds = $this->getCategoryIdsWithChildren((int) $filters['category_id']);
            $query->whereIn('category_id', $categoryIds);
        }

        // Multiple brands filter
        if (!empty($filters['brand_ids'])) {
            $brandIds = is_array($filters['brand_ids']) ? $filters['brand_ids'] : explode(',', $filters['brand_ids']);
            $query->whereIn('brand_id', $brandIds);
        } elseif (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        // Price range filter
        if (!empty($filters['price_min'])) {
            $query->where('base_price', '>=', (float) $filters['price_min']);
        }
        if (!empty($filters['price_max'])) {
            $query->where('base_price', '<=', (float) $filters['price_max']);
        }

        // Attribute values filter (filter by product variants that have specific attribute values)
        if (!empty($filters['attribute_values'])) {
            $attributeValues = is_array($filters['attribute_values'])
                ? $filters['attribute_values']
                : explode(',', $filters['attribute_values']);

            $query->whereHas('variants.attributeValues', function ($q) use ($attributeValues) {
                $q->whereIn('attribute_values.id', $attributeValues);
            });
        }

        // Specifications filter (key-value pairs)
        if (!empty($filters['specifications'])) {
            foreach ($filters['specifications'] as $specName => $specValue) {
                $query->whereHas('specifications', function ($q) use ($specName, $specValue) {
                    $q->where('name', $specName)->where('value', $specValue);
                });
            }
        }

        // In stock filter
        if (!empty($filters['in_stock'])) {
            $query->whereHas('variants', function ($q) {
                $q->where('stock_quantity', '>', 0)->where('status', 'active');
            });
        }

        // Has discount filter (Акції) - products with active discount
        if (!empty($filters['has_discount'])) {
            $query->where(function ($q) {
                $q->whereNotNull('discount_price')
                  ->where('discount_price', '>', 0)
                  ->where(function ($q2) {
                      $q2->whereNull('discount_starts_at')
                         ->orWhere('discount_starts_at', '<=', now());
                  })
                  ->where(function ($q2) {
                      $q2->whereNull('discount_ends_at')
                         ->orWhere('discount_ends_at', '>=', now());
                  });
            });
        }

        // Is clearance filter (Уцінка)
        if (!empty($filters['is_clearance'])) {
            $query->where('is_clearance', true);
        }

        // Sort by created_at desc by default
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        // Handle special sort options
        if ($sortBy === 'price_asc') {
            $query->orderBy('base_price', 'asc');
        } elseif ($sortBy === 'price_desc') {
            $query->orderBy('base_price', 'desc');
        } elseif ($sortBy === 'name') {
            $query->orderBy('name', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

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
     * Get category IDs including all child categories.
     *
     * @param int $categoryId
     * @return array
     */
    private function getCategoryIdsWithChildren(int $categoryId): array
    {
        $ids = [$categoryId];
        $children = ProductCategory::where('parent_id', $categoryId)->pluck('id')->toArray();

        foreach ($children as $childId) {
            $ids = array_merge($ids, $this->getCategoryIdsWithChildren($childId));
        }

        return $ids;
    }

    /**
     * Get available filters based on current filter selection.
     *
     * @param array $currentFilters
     * @return array
     */
    public function getAvailableFilters(array $currentFilters = []): array
    {
        $categoryIds = [];
        if (!empty($currentFilters['category_id'])) {
            $categoryIds = $this->getCategoryIdsWithChildren((int) $currentFilters['category_id']);
        }

        // Get price range
        $priceQuery = Product::where('status', 'published');
        if (!empty($categoryIds)) {
            $priceQuery->whereIn('category_id', $categoryIds);
        }
        $priceRange = $priceQuery->selectRaw('MIN(base_price) as min_price, MAX(base_price) as max_price')->first();

        // Get available categories with counts using simpler approach
        $categoriesQuery = ProductCategory::whereNull('parent_id')
            ->with(['subcategories' => function ($q) {
                $q->withCount(['products' => function ($query) {
                    $query->where('status', 'published');
                }]);
            }])
            ->withCount(['products' => function ($query) {
                $query->where('status', 'published');
            }])
            ->orderBy('name');

        $categories = $categoriesQuery->get()->filter(function ($category) {
            // Keep category if it has products or any subcategory has products
            if ($category->products_count > 0) {
                return true;
            }
            if ($category->subcategories) {
                return $category->subcategories->contains(function ($sub) {
                    return $sub->products_count > 0;
                });
            }
            return false;
        })->values();

        // Get available brands with counts
        $brandsQuery = ProductBrand::whereHas('products', function ($q) use ($categoryIds) {
            $q->where('status', 'published');
            if (!empty($categoryIds)) {
                $q->whereIn('category_id', $categoryIds);
            }
        })
        ->withCount(['products' => function ($q) use ($categoryIds) {
            $q->where('status', 'published');
            if (!empty($categoryIds)) {
                $q->whereIn('category_id', $categoryIds);
            }
        }])
        ->orderBy('name');

        $brands = $brandsQuery->get();

        // Get available attributes with values using simpler queries
        $attributes = collect([]);
        $attributeQuery = Attribute::with(['values' => function ($q) {
            $q->orderBy('sort_order');
        }])->orderBy('name')->get();

        foreach ($attributeQuery as $attribute) {
            $filteredValues = collect([]);
            foreach ($attribute->values as $value) {
                // Count variants with this value that belong to published products
                $count = DB::table('product_variant_attribute_values')
                    ->join('product_variants', 'product_variant_attribute_values.product_variant_id', '=', 'product_variants.id')
                    ->join('products', 'product_variants.product_id', '=', 'products.id')
                    ->where('product_variant_attribute_values.attribute_value_id', $value->id)
                    ->where('products.status', 'published')
                    ->when(!empty($categoryIds), function ($q) use ($categoryIds) {
                        $q->whereIn('products.category_id', $categoryIds);
                    })
                    ->count();

                if ($count > 0) {
                    $value->variants_count = $count;
                    $filteredValues->push($value);
                }
            }

            if ($filteredValues->isNotEmpty()) {
                $attribute->setRelation('values', $filteredValues);
                $attributes->push($attribute);
            }
        }

        // Get unique specifications
        $specifications = DB::table('product_specifications')
            ->join('products', 'product_specifications.product_id', '=', 'products.id')
            ->where('products.status', 'published')
            ->when(!empty($categoryIds), function ($q) use ($categoryIds) {
                $q->whereIn('products.category_id', $categoryIds);
            })
            ->select('product_specifications.name')
            ->selectRaw('GROUP_CONCAT(DISTINCT product_specifications.value ORDER BY product_specifications.value) as available_values')
            ->selectRaw('COUNT(DISTINCT products.id) as products_count')
            ->groupBy('product_specifications.name')
            ->orderBy('product_specifications.name')
            ->get()
            ->map(function ($spec) {
                return [
                    'name' => $spec->name,
                    'values' => explode(',', $spec->available_values),
                    'products_count' => $spec->products_count,
                ];
            });

        return [
            'price_range' => [
                'min' => (float) ($priceRange->min_price ?? 0),
                'max' => (float) ($priceRange->max_price ?? 0),
            ],
            'categories' => $categories,
            'brands' => $brands,
            'attributes' => $attributes,
            'specifications' => $specifications,
        ];
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
            'category.parent.parent',
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
            'category.parent.parent',
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
