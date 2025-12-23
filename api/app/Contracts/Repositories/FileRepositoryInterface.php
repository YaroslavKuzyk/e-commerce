<?php

namespace App\Contracts\Repositories;

use App\Models\File;
use Illuminate\Database\Eloquent\Collection;

interface FileRepositoryInterface
{
    /**
     * Get all files with user relationship.
     *
     * @param array $filters
     * @return Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllWithUser(array $filters = []);

    /**
     * Find a file by ID with user relationship.
     *
     * @param int $id
     * @return File|null
     */
    public function findWithUser(int $id): ?File;

    /**
     * Create a new file record.
     *
     * @param array $data
     * @return File
     */
    public function create(array $data): File;

    /**
     * Delete a file by ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Check if file exists.
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool;
}
