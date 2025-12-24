<?php

namespace App\Services;

use App\Contracts\CustomerProductServiceInterface;
use App\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerProductService implements CustomerProductServiceInterface
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Get paginated products.
     *
     * @param int $page
     * @param int $limit
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getProductsPaginated(int $page = 1, int $limit = 15, array $filters = []): LengthAwarePaginator
    {
        $filters['page'] = $page;
        $filters['per_page'] = $limit;
        $filters['status'] = 'published';

        return $this->productRepository->getAllWithFilters($filters);
    }

    /**
     * Get product by ID.
     *
     * @param int $id
     * @return Product
     * @throws \Exception
     */
    public function getProductById(int $id): Product
    {
        $product = $this->productRepository->findById($id);

        if (!$product || $product->status->value !== 'published') {
            throw new \Exception('Product not found');
        }

        return $product;
    }

    /**
     * Get product by slug.
     *
     * @param string $slug
     * @return Product
     * @throws \Exception
     */
    public function getProductBySlug(string $slug): Product
    {
        $product = $this->productRepository->findBySlug($slug);

        if (!$product || $product->status->value !== 'published') {
            throw new \Exception('Product not found');
        }

        return $product;
    }

    /**
     * Get available filters for products.
     *
     * @param array $currentFilters
     * @return array
     */
    public function getAvailableFilters(array $currentFilters = []): array
    {
        return $this->productRepository->getAvailableFilters($currentFilters);
    }

    /**
     * Get paginated product variants.
     *
     * @param int $page
     * @param int $limit
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getVariantsPaginated(int $page = 1, int $limit = 15, array $filters = []): LengthAwarePaginator
    {
        $filters['page'] = $page;
        $filters['per_page'] = $limit;

        return $this->productRepository->getVariantsWithFilters($filters);
    }
}
