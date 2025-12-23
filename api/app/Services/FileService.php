<?php

namespace App\Services;

use App\Contracts\Repositories\FileRepositoryInterface;
use App\Models\File;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    /**
     * @var FileRepositoryInterface
     */
    protected FileRepositoryInterface $fileRepository;

    /**
     * FileService constructor.
     *
     * @param FileRepositoryInterface $fileRepository
     */
    public function __construct(FileRepositoryInterface $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    /**
     * Get all files with user information.
     *
     * @param array $filters
     * @return Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllFiles(array $filters = [])
    {
        return $this->fileRepository->getAllWithUser($filters);
    }

    /**
     * Get a single file with user information.
     *
     * @param int $id
     * @return File|null
     */
    public function getFile(int $id): ?File
    {
        return $this->fileRepository->findWithUser($id);
    }

    /**
     * Upload and store a file.
     *
     * @param UploadedFile $uploadedFile
     * @param int $userId
     * @return File
     */
    public function uploadFile(UploadedFile $uploadedFile, int $userId): File
    {
        // Generate unique file name
        $extension = $uploadedFile->getClientOriginalExtension();
        $fileName = Str::uuid() . '.' . $extension;

        // Store file in private storage
        $path = $uploadedFile->storeAs('files', $fileName, 'local');

        // Create file record
        return $this->fileRepository->create([
            'name' => $fileName,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'path' => $path,
            'user_id' => $userId,
        ]);
    }

    /**
     * Delete a file and its physical file.
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteFile(int $id): bool
    {
        $file = $this->fileRepository->findWithUser($id);

        if (!$file) {
            throw new \Exception('File not found');
        }

        // Delete physical file
        if (Storage::disk('local')->exists($file->path)) {
            Storage::disk('local')->delete($file->path);
        }

        // Delete database record
        return $this->fileRepository->delete($id);
    }

    /**
     * Delete multiple files and their physical files.
     *
     * @param array $ids
     * @return void
     * @throws \Exception
     */
    public function deleteFiles(array $ids): void
    {
        foreach ($ids as $id) {
            $this->deleteFile($id);
        }
    }

    /**
     * Get the full storage path of a file.
     *
     * @param int $id
     * @return string
     * @throws \Exception
     */
    public function getFilePath(int $id): string
    {
        $file = $this->fileRepository->findWithUser($id);

        if (!$file) {
            throw new \Exception('File not found');
        }

        // Use Storage facade to get the correct path for the 'local' disk
        $fullPath = Storage::disk('local')->path($file->path);

        if (!file_exists($fullPath)) {
            throw new \Exception('Physical file not found at: ' . $fullPath);
        }

        return $fullPath;
    }

    /**
     * Get file metadata for download response.
     *
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getFileMetadata(int $id): array
    {
        $file = $this->fileRepository->findWithUser($id);

        if (!$file) {
            throw new \Exception('File not found');
        }

        return [
            'path' => $this->getFilePath($id),
            'mime_type' => $file->mime_type,
            'original_name' => $file->original_name,
        ];
    }

    /**
     * Check if file exists.
     *
     * @param int $id
     * @return bool
     */
    public function fileExists(int $id): bool
    {
        return $this->fileRepository->exists($id);
    }
}
