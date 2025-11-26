<?php

namespace App\Repositories;

use App\Contracts\AttributeRepositoryInterface;
use App\Models\Attribute;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AttributeRepository implements AttributeRepositoryInterface
{
    /**
     * Get all attributes.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Attribute::with('values')->orderBy('sort_order')->orderBy('name')->get();
    }

    /**
     * Get all attributes with filters and optional pagination.
     *
     * @param array $filters
     * @return Collection|LengthAwarePaginator
     */
    public function getAllWithFilters(array $filters): Collection|LengthAwarePaginator
    {
        $query = Attribute::with('values');

        if (!empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }

        if (!empty($filters['slug'])) {
            $query->where('slug', 'like', "%{$filters['slug']}%");
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->orderBy('sort_order')->orderBy('name');

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
     * Find an attribute by ID.
     *
     * @param int $id
     * @return Attribute|null
     */
    public function findById(int $id): ?Attribute
    {
        return Attribute::with('values')->find($id);
    }

    /**
     * Find an attribute by slug.
     *
     * @param string $slug
     * @return Attribute|null
     */
    public function findBySlug(string $slug): ?Attribute
    {
        return Attribute::with('values')->where('slug', $slug)->first();
    }

    /**
     * Create a new attribute.
     *
     * @param array $data
     * @return Attribute
     */
    public function create(array $data): Attribute
    {
        return Attribute::create($data);
    }

    /**
     * Update an attribute.
     *
     * @param Attribute $attribute
     * @param array $data
     * @return Attribute
     */
    public function update(Attribute $attribute, array $data): Attribute
    {
        $attribute->update($data);
        return $attribute->fresh()->load('values');
    }

    /**
     * Delete an attribute.
     *
     * @param Attribute $attribute
     * @return bool
     */
    public function delete(Attribute $attribute): bool
    {
        return $attribute->delete();
    }
}
