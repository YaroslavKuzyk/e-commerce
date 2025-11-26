<?php

namespace App\Contracts;

use App\Models\Attribute;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AttributeRepositoryInterface
{
    public function getAll(): Collection;

    public function getAllWithFilters(array $filters): Collection|LengthAwarePaginator;

    public function findById(int $id): ?Attribute;

    public function findBySlug(string $slug): ?Attribute;

    public function create(array $data): Attribute;

    public function update(Attribute $attribute, array $data): Attribute;

    public function delete(Attribute $attribute): bool;
}
