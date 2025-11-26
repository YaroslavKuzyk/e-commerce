<?php

namespace App\Contracts;

use App\Models\Attribute;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AdminAttributeServiceInterface
{
    public function getAllAttributes(array $filters = []): Collection|LengthAwarePaginator;

    public function getAttributeById(int $id): Attribute;

    public function createAttribute(array $data): Attribute;

    public function updateAttribute(int $id, array $data): Attribute;

    public function deleteAttribute(int $id): bool;

    public function addValue(int $attributeId, array $data): Attribute;

    public function updateValue(int $attributeId, int $valueId, array $data): Attribute;

    public function deleteValue(int $attributeId, int $valueId): bool;
}
