<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\AdminCatalogMenuServiceInterface;
use App\Http\Resources\CatalogMenuResource;
use App\Http\Resources\CatalogMenuSectionResource;
use App\Http\Resources\CatalogMenuItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdminCatalogMenuController extends Controller
{
    public function __construct(
        private AdminCatalogMenuServiceInterface $adminCatalogMenuService
    ) {}

    /**
     * Get catalog menu for a category.
     */
    public function show(int $categoryId): JsonResponse
    {
        $menu = $this->adminCatalogMenuService->getMenuByCategoryId($categoryId);

        return response()->json([
            'success' => true,
            'data' => $menu ? new CatalogMenuResource($menu) : null,
        ]);
    }

    /**
     * Create or update catalog menu for a category.
     */
    public function store(Request $request, int $categoryId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'is_enabled' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $menu = $this->adminCatalogMenuService->createOrUpdateMenu($categoryId, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Catalog menu saved successfully',
                'data' => new CatalogMenuResource($menu),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete catalog menu for a category.
     */
    public function destroy(int $categoryId): JsonResponse
    {
        try {
            $this->adminCatalogMenuService->deleteMenu($categoryId);

            return response()->json([
                'success' => true,
                'message' => 'Catalog menu deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add a section to a menu.
     */
    public function addSection(Request $request, int $menuId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'column_index' => 'required|integer|min:1|max:3',
            'name' => 'required|string|max:255',
            'link' => 'nullable|string|max:500',
            'icon_file_id' => 'nullable|integer|exists:files,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $section = $this->adminCatalogMenuService->addSection($menuId, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Section added successfully',
                'data' => new CatalogMenuSectionResource($section),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a section.
     */
    public function updateSection(Request $request, int $sectionId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'column_index' => 'sometimes|integer|min:1|max:3',
            'name' => 'sometimes|required|string|max:255',
            'link' => 'nullable|string|max:500',
            'icon_file_id' => 'nullable|integer|exists:files,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $section = $this->adminCatalogMenuService->updateSection($sectionId, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Section updated successfully',
                'data' => new CatalogMenuSectionResource($section),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a section.
     */
    public function deleteSection(int $sectionId): JsonResponse
    {
        try {
            $this->adminCatalogMenuService->deleteSection($sectionId);

            return response()->json([
                'success' => true,
                'message' => 'Section deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reorder sections within a column.
     */
    public function reorderSections(Request $request, int $menuId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'column' => 'required|integer|min:1|max:3',
            'section_ids' => 'required|array',
            'section_ids.*' => 'integer|exists:catalog_menu_sections,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $sections = $this->adminCatalogMenuService->reorderSections(
                $menuId,
                $request->input('column'),
                $request->input('section_ids')
            );

            return response()->json([
                'success' => true,
                'message' => 'Sections reordered successfully',
                'data' => CatalogMenuSectionResource::collection($sections),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add an item to a section.
     */
    public function addItem(Request $request, int $sectionId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'link' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $item = $this->adminCatalogMenuService->addItem($sectionId, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Item added successfully',
                'data' => new CatalogMenuItemResource($item),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an item.
     */
    public function updateItem(Request $request, int $itemId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'link' => 'sometimes|required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $item = $this->adminCatalogMenuService->updateItem($itemId, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully',
                'data' => new CatalogMenuItemResource($item),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an item.
     */
    public function deleteItem(int $itemId): JsonResponse
    {
        try {
            $this->adminCatalogMenuService->deleteItem($itemId);

            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reorder items within a section.
     */
    public function reorderItems(Request $request, int $sectionId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'item_ids' => 'required|array',
            'item_ids.*' => 'integer|exists:catalog_menu_items,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $items = $this->adminCatalogMenuService->reorderItems($sectionId, $request->input('item_ids'));

            return response()->json([
                'success' => true,
                'message' => 'Items reordered successfully',
                'data' => CatalogMenuItemResource::collection($items),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
