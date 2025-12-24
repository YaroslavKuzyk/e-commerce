<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductVariantCatalogResource;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerCartController extends Controller
{
    /**
     * Get user's cart with product variants and quantities.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $cartItems = $user->cartItems()
            ->with(['productVariant' => function ($query) {
                $query->where('status', 'published')
                    ->with([
                        'product.category.parent',
                        'product.brand',
                        'product.mainImage',
                        'attributeValues.attribute',
                        'images.file',
                    ]);
            }])
            ->get()
            ->filter(fn($item) => $item->productVariant !== null && $item->productVariant->product !== null);

        $items = $cartItems->map(function ($item) {
            return [
                'id' => $item->id,
                'variant' => new ProductVariantCatalogResource($item->productVariant),
                'quantity' => $item->quantity,
                'created_at' => $item->created_at,
            ];
        });

        $totalQuantity = $cartItems->sum('quantity');
        $totalPrice = $cartItems->sum(function ($item) {
            return $item->productVariant->current_price * $item->quantity;
        });

        return response()->json([
            'success' => true,
            'data' => $items->values(),
            'meta' => [
                'total_items' => $cartItems->count(),
                'total_quantity' => $totalQuantity,
                'total_price' => $totalPrice,
            ],
        ]);
    }

    /**
     * Get cart summary (IDs and quantities for quick checking).
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        $cartItems = $user->cartItems()
            ->select('product_variant_id', 'quantity')
            ->get()
            ->keyBy('product_variant_id')
            ->map(fn($item) => $item->quantity);

        return response()->json([
            'success' => true,
            'data' => $cartItems,
            'meta' => [
                'total_items' => $cartItems->count(),
                'total_quantity' => $cartItems->sum(),
            ],
        ]);
    }

    /**
     * Add product variant to cart or update quantity if exists.
     */
    public function store(Request $request, int $variantId): JsonResponse
    {
        $request->validate([
            'quantity' => 'sometimes|integer|min:1|max:999',
        ]);

        $user = $request->user();
        $quantity = (int) $request->input('quantity', 1);

        // Check if variant exists and is published
        $variant = ProductVariant::where('id', $variantId)
            ->where('status', 'published')
            ->first();

        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Product variant not found',
            ], 404);
        }

        // Check if item already exists in cart
        $existingItem = CartItem::where('user_id', $user->id)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($existingItem) {
            // Add to existing quantity
            $existingItem->increment('quantity', $quantity);
            $cartItem = $existingItem;
        } else {
            // Create new cart item
            $cartItem = CartItem::create([
                'user_id' => $user->id,
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'data' => [
                'variant_id' => $variantId,
                'quantity' => $cartItem->quantity,
            ],
        ], 201);
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, int $variantId): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:999',
        ]);

        $user = $request->user();
        $quantity = (int) $request->input('quantity');

        $cartItem = CartItem::where('user_id', $user->id)
            ->where('product_variant_id', $variantId)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
            ], 404);
        }

        $cartItem->update(['quantity' => $quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated',
            'data' => [
                'variant_id' => $variantId,
                'quantity' => $quantity,
            ],
        ]);
    }

    /**
     * Remove product variant from cart.
     */
    public function destroy(Request $request, int $variantId): JsonResponse
    {
        $user = $request->user();

        CartItem::where('user_id', $user->id)
            ->where('product_variant_id', $variantId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product removed from cart',
        ]);
    }

    /**
     * Clear entire cart.
     */
    public function clear(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->cartItems()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared',
        ]);
    }

    /**
     * Sync cart from localStorage (merge with existing).
     * Used when guest user logs in.
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.variant_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1|max:999',
        ]);

        $user = $request->user();
        $items = $request->input('items');

        // Get variant IDs from request
        $variantIds = array_column($items, 'variant_id');

        // Get only valid published variant IDs
        $validVariantIds = ProductVariant::whereIn('id', $variantIds)
            ->where('status', 'published')
            ->pluck('id')
            ->toArray();

        // Create a map of variant_id => quantity from request
        $itemsMap = [];
        foreach ($items as $item) {
            if (in_array($item['variant_id'], $validVariantIds)) {
                $itemsMap[$item['variant_id']] = $item['quantity'];
            }
        }

        // Merge with existing cart (add quantities for existing items)
        foreach ($itemsMap as $variantId => $quantity) {
            $existingItem = CartItem::where('user_id', $user->id)
                ->where('product_variant_id', $variantId)
                ->first();

            if ($existingItem) {
                // Add to existing quantity
                $existingItem->update([
                    'quantity' => min($existingItem->quantity + $quantity, 999),
                ]);
            } else {
                // Create new cart item
                CartItem::create([
                    'user_id' => $user->id,
                    'product_variant_id' => $variantId,
                    'quantity' => $quantity,
                ]);
            }
        }

        // Return updated cart summary
        $cartItems = $user->cartItems()
            ->select('product_variant_id', 'quantity')
            ->get()
            ->keyBy('product_variant_id')
            ->map(fn($item) => $item->quantity);

        return response()->json([
            'success' => true,
            'message' => 'Cart synced successfully',
            'data' => $cartItems,
            'meta' => [
                'total_items' => $cartItems->count(),
                'total_quantity' => $cartItems->sum(),
            ],
        ]);
    }
}
