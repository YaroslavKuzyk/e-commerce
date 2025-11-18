<?php

namespace App\Repositories;

use App\Contracts\FileRepositoryInterface;
use App\Models\File;
use Illuminate\Database\Eloquent\Collection;

class FileRepository implements FileRepositoryInterface
{
    /**
     * @var File
     */
    protected File $model;

    /**
     * FileRepository constructor.
     *
     * @param File $model
     */
    public function __construct(File $model)
    {
        $this->model = $model;
    }

    /**
     * Get all files with user relationship.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllWithUser(array $filters = []): Collection
    {
        $query = $this->model->with('user:id,name,email');

        // Filter by filename
        if (!empty($filters['search'])) {
            $query->where('original_name', 'like', '%' . $filters['search'] . '%');
        }

        // Filter by user name
        if (!empty($filters['user_search'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['user_search'] . '%');
            });
        }

        // Filter by file types
        if (!empty($filters['types']) && is_array($filters['types'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['types'] as $type) {
                    switch ($type) {
                        case 'image':
                            $q->orWhere('mime_type', 'like', 'image/%');
                            break;
                        case 'video':
                            $q->orWhere('mime_type', 'like', 'video/%');
                            break;
                        case 'audio':
                            $q->orWhere('mime_type', 'like', 'audio/%');
                            break;
                        case 'pdf':
                            $q->orWhere('mime_type', 'like', '%pdf%');
                            break;
                        case 'archive':
                            $q->orWhere('mime_type', 'like', '%zip%')
                              ->orWhere('mime_type', 'like', '%archive%')
                              ->orWhere('mime_type', 'like', '%rar%')
                              ->orWhere('mime_type', 'like', '%7z%')
                              ->orWhere('mime_type', 'like', '%tar%')
                              ->orWhere('mime_type', 'like', '%gzip%');
                            break;
                        case 'other':
                            $q->orWhere(function ($subQ) {
                                $subQ->where('mime_type', 'not like', 'image/%')
                                     ->where('mime_type', 'not like', 'video/%')
                                     ->where('mime_type', 'not like', 'audio/%')
                                     ->where('mime_type', 'not like', '%pdf%')
                                     ->where('mime_type', 'not like', '%zip%')
                                     ->where('mime_type', 'not like', '%archive%')
                                     ->where('mime_type', 'not like', '%rar%')
                                     ->where('mime_type', 'not like', '%7z%')
                                     ->where('mime_type', 'not like', '%tar%')
                                     ->where('mime_type', 'not like', '%gzip%');
                            });
                            break;
                    }
                }
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Find a file by ID with user relationship.
     *
     * @param int $id
     * @return File|null
     */
    public function findWithUser(int $id): ?File
    {
        return $this->model
            ->with('user:id,name,email')
            ->find($id);
    }

    /**
     * Create a new file record.
     *
     * @param array $data
     * @return File
     */
    public function create(array $data): File
    {
        $file = $this->model->create($data);
        $file->load('user:id,name,email');

        return $file;
    }

    /**
     * Delete a file by ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $file = $this->model->find($id);

        if (!$file) {
            return false;
        }

        return $file->delete();
    }

    /**
     * Check if file exists.
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool
    {
        return $this->model->where('id', $id)->exists();
    }
}
