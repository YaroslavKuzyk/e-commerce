<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerFileController extends Controller
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Download the specified file (public access).
     *
     * @param int $id
     * @return BinaryFileResponse|JsonResponse
     */
    public function download(int $id): BinaryFileResponse|JsonResponse
    {
        try {
            $metadata = $this->fileService->getFileMetadata($id);

            return response()->file($metadata['path'], [
                'Content-Type' => $metadata['mime_type'],
                'Content-Disposition' => 'inline; filename="' . $metadata['original_name'] . '"',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
