<?php

namespace App\Services\Admin;

use App\Contracts\AdminAttributeServiceInterface;
use App\Contracts\AttributeRepositoryInterface;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AdminAttributeService implements AdminAttributeServiceInterface
{
    public function __construct(
        protected AttributeRepositoryInterface $attributeRepository
    ) {}

    /**
     * Get all attributes with optional filters and pagination.
     *
     * @param array $filters
     * @return Collection|LengthAwarePaginator
     */
    public function getAllAttributes(array $filters = []): Collection|LengthAwarePaginator
    {
        if (empty($filters)) {
            return $this->attributeRepository->getAll();
        }

        return $this->attributeRepository->getAllWithFilters($filters);
    }

    /**
     * Get an attribute by ID.
     *
     * @param int $id
     * @return Attribute
     * @throws \Exception
     */
    public function getAttributeById(int $id): Attribute
    {
        $attribute = $this->attributeRepository->findById($id);

        if (!$attribute) {
            throw new \Exception('Attribute not found');
        }

        return $attribute;
    }

    /**
     * Create a new attribute.
     *
     * @param array $data
     * @return Attribute
     */
    public function createAttribute(array $data): Attribute
    {
        $attribute = $this->attributeRepository->create($data);

        // Create values if provided
        if (!empty($data['values']) && is_array($data['values'])) {
            foreach ($data['values'] as $index => $valueData) {
                $attribute->values()->create([
                    'value' => $valueData['value'],
                    'slug' => $valueData['slug'],
                    'color_code' => $valueData['color_code'] ?? null,
                    'sort_order' => $valueData['sort_order'] ?? $index,
                ]);
            }
        }

        return $attribute->fresh()->load('values');
    }

    /**
     * Update an attribute.
     *
     * @param int $id
     * @param array $data
     * @return Attribute
     * @throws \Exception
     */
    public function updateAttribute(int $id, array $data): Attribute
    {
        $attribute = $this->getAttributeById($id);

        return $this->attributeRepository->update($attribute, $data);
    }

    /**
     * Delete an attribute.
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteAttribute(int $id): bool
    {
        $attribute = $this->getAttributeById($id);

        return $this->attributeRepository->delete($attribute);
    }

    /**
     * Add a value to an attribute.
     *
     * @param int $attributeId
     * @param array $data
     * @return Attribute
     * @throws \Exception
     */
    public function addValue(int $attributeId, array $data): Attribute
    {
        $attribute = $this->getAttributeById($attributeId);

        $attribute->values()->create([
            'value' => $data['value'],
            'slug' => $data['slug'],
            'color_code' => $data['color_code'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return $attribute->fresh()->load('values');
    }

    /**
     * Update a value of an attribute.
     *
     * @param int $attributeId
     * @param int $valueId
     * @param array $data
     * @return Attribute
     * @throws \Exception
     */
    public function updateValue(int $attributeId, int $valueId, array $data): Attribute
    {
        $attribute = $this->getAttributeById($attributeId);

        $value = $attribute->values()->find($valueId);

        if (!$value) {
            throw new \Exception('Attribute value not found');
        }

        $value->update($data);

        return $attribute->fresh()->load('values');
    }

    /**
     * Delete a value from an attribute.
     *
     * @param int $attributeId
     * @param int $valueId
     * @return bool
     * @throws \Exception
     */
    public function deleteValue(int $attributeId, int $valueId): bool
    {
        $attribute = $this->getAttributeById($attributeId);

        $value = $attribute->values()->find($valueId);

        if (!$value) {
            throw new \Exception('Attribute value not found');
        }

        return $value->delete();
    }
}
