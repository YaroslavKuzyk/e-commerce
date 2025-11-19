<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\FileCollection;
use App\Http\Resources\FileResource;
use App\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminFileController extends Controller
{
    /**
     * @var FileService
     */
    protected FileService $fileService;

    /**
     * AdminFileController constructor.
     *
     * @param FileService $fileService
     */
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Display a listing of files.
     *
     * @OA\Get(
     *     path="/api/admin/files",
     *     tags={"Admin Files"},
     *     summary="Get all files with optional filters and pagination",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by filename",
     *         required=false,
     *         @OA\Schema(type="string", example="example.jpg")
     *     ),
     *     @OA\Parameter(
     *         name="user_search",
     *         in="query",
     *         description="Search by user name",
     *         required=false,
     *         @OA\Schema(type="string", example="John Doe")
     *     ),
     *     @OA\Parameter(
     *         name="types",
     *         in="query",
     *         description="Filter by file types (comma-separated: image,video,audio,pdf,archive,other)",
     *         required=false,
     *         @OA\Schema(type="string", example="image,video")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page (if not provided, returns all items)",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Files retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="original_name", type="string", example="example.jpg"),
     *                     @OA\Property(property="name", type="string", example="1234567890_example.jpg"),
     *                     @OA\Property(property="path", type="string", example="uploads/files/1234567890_example.jpg"),
     *                     @OA\Property(property="mime_type", type="string", example="image/jpeg"),
     *                     @OA\Property(property="size", type="integer", example=102400),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john@example.com")
     *                     ),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-18T10:30:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-18T10:30:00.000000Z")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=73)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'search' => $request->query('search'),
            'user_search' => $request->query('user_search'),
            'types' => $request->query('types') ? explode(',', $request->query('types')) : [],
            'page' => $request->query('page', 1),
            'per_page' => $request->query('per_page'),
        ];

        $result = $this->fileService->getAllFiles($filters);

        // If pagination is used (per_page is provided)
        if ($filters['per_page']) {
            return response()->json([
                'success' => true,
                'data' => FileResource::collection($result->items()),
                'meta' => [
                    'current_page' => $result->currentPage(),
                    'last_page' => $result->lastPage(),
                    'per_page' => $result->perPage(),
                    'total' => $result->total(),
                ],
            ]);
        }

        // If no pagination (return all items)
        return response()->json([
            'success' => true,
            'data' => FileResource::collection($result),
        ]);
    }

    /**
     * Store a newly uploaded file.
     *
     * @OA\Post(
     *     path="/api/admin/files",
     *     tags={"Admin Files"},
     *     summary="Upload a new file",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="File to upload (max 10MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="File uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="original_name", type="string", example="example.jpg"),
     *                 @OA\Property(property="name", type="string", example="1234567890_example.jpg"),
     *                 @OA\Property(property="path", type="string", example="uploads/files/1234567890_example.jpg"),
     *                 @OA\Property(property="mime_type", type="string", example="image/jpeg"),
     *                 @OA\Property(property="size", type="integer", example=102400),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="message", type="string", example="File uploaded successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The file field is required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to upload file: Error message")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
        ]);

        try {
            $file = $this->fileService->uploadFile(
                $request->file('file'),
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'data' => new FileResource($file),
                'message' => 'File uploaded successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified file metadata.
     *
     * @OA\Get(
     *     path="/api/admin/files/{id}",
     *     tags={"Admin Files"},
     *     summary="Get file metadata by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="File ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File metadata retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="original_name", type="string", example="example.jpg"),
     *                 @OA\Property(property="name", type="string", example="1234567890_example.jpg"),
     *                 @OA\Property(property="path", type="string", example="uploads/files/1234567890_example.jpg"),
     *                 @OA\Property(property="mime_type", type="string", example="image/jpeg"),
     *                 @OA\Property(property="size", type="integer", example=102400),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(
     *         response=404,
     *         description="File not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="File not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to retrieve file: Error message")
     *         )
     *     )
     * )
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $file = $this->fileService->getFile($id);

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new FileResource($file),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download the specified file.
     *
     * @OA\Get(
     *     path="/api/admin/files/{id}/download",
     *     tags={"Admin Files"},
     *     summary="Download file by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="File ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File content returned",
     *         @OA\MediaType(
     *             mediaType="application/octet-stream",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(
     *         response=404,
     *         description="File not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="File not found")
     *         )
     *     )
     * )
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

    /**
     * Remove the specified file.
     *
     * @OA\Delete(
     *     path="/api/admin/files/{id}",
     *     tags={"Admin Files"},
     *     summary="Delete file by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="File ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="File deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(
     *         response=404,
     *         description="File not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="File not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to delete file: Error message")
     *         )
     *     )
     * )
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->fileService->deleteFile($id);

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove multiple files.
     *
     * @OA\Post(
     *     path="/api/admin/files/bulk-delete",
     *     tags={"Admin Files"},
     *     summary="Delete multiple files by IDs",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"ids"},
     *             @OA\Property(
     *                 property="ids",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={1, 2, 3}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Files deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Files deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The ids field is required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to delete files: Error message")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:files,id',
        ]);

        try {
            $this->fileService->deleteFiles($request->input('ids'));

            return response()->json([
                'success' => true,
                'message' => 'Files deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete files: ' . $e->getMessage(),
            ], 500);
        }
    }
}
