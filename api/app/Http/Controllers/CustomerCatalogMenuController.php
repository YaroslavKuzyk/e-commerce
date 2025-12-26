<?php

namespace App\Http\Controllers;

use App\Contracts\CustomerCatalogMenuServiceInterface;
use App\Http\Resources\CatalogMenuResource;
use Illuminate\Http\JsonResponse;

class CustomerCatalogMenuController extends Controller
{
    public function __construct(
        private CustomerCatalogMenuServiceInterface $customerCatalogMenuService
    ) {}

    /**
     * Get catalog menu for a category (customer-facing).
     */
    public function show(int $categoryId): JsonResponse
    {
        $menu = $this->customerCatalogMenuService->getMenuByCategoryId($categoryId);

        return response()->json([
            'success' => true,
            'data' => $menu ? new CatalogMenuResource($menu) : null,
        ]);
    }

    /**
     * Get all catalog menus for root categories.
     */
    public function index(): JsonResponse
    {
        $menus = $this->customerCatalogMenuService->getAllMenus();

        // Return as a map of category_id => menu
        $menusMap = [];
        foreach ($menus as $menu) {
            $menusMap[$menu->category_id] = new CatalogMenuResource($menu);
        }

        return response()->json([
            'success' => true,
            'data' => $menusMap,
        ]);
    }
}
