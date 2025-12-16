<?php

namespace App\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerProductServiceInterface
{
    /**
     * Get paginated products.
     *
     * @param int $page
     * @param int $limit
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getProductsPaginated(int $page = 1, int $limit = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Get product by ID.
     *
     * @param int $id
     * @return Product
     * @throws \Exception
     */
    public function getProductById(int $id): Product;

    /**
     * Get product by slug.
     *
     * @param string $slug
     * @return Product
     * @throws \Exception
     */
    public function getProductBySlug(string $slug): Product;

    /**
     * Get available filters for products.
     *
     * @param array $currentFilters
     * @return array
     */
    public function getAvailableFilters(array $currentFilters = []): array;
}
