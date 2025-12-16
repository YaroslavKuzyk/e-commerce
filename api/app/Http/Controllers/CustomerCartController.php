<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerCartController extends Controller
{
    /**
     * Get user's cart with products and quantities.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $cartItems = $user->cartItems()
            ->with(['product' => function ($query) {
                $query->published()
                    ->with(['category', 'brand', 'mainImage', 'variants.images', 'attributes.values']);
            }])
            ->get()
            ->filter(fn($item) => $item->product !== null);

        $items = $cartItems->map(function ($item) {
            return [
                'id' => $item->id,
                'product' => new ProductResource($item->product),
                'quantity' => $item->quantity,
                'created_at' => $item->created_at,
            ];
        });

        $totalQuantity = $cartItems->sum('quantity');
        $totalPrice = $cartItems->sum(function ($item) {
            $price = $item->product->discount_price ?? $item->product->price;
            return $price * $item->quantity;
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
            ->select('product_id', 'quantity')
            ->get()
            ->keyBy('product_id')
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
     * Add product to cart or update quantity if exists.
     */
    public function store(Request $request, int $productId): JsonResponse
    {
        $request->validate([
            'quantity' => 'sometimes|integer|min:1|max:999',
        ]);

        $user = $request->user();
        $quantity = (int) $request->input('quantity', 1);

        // Check if product exists and is published
        $product = Product::where('id', $productId)->published()->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        // Add or update cart item
        $cartItem = CartItem::updateOrCreate(
            [
                'user_id' => $user->id,
                'product_id' => $productId,
            ],
            [
                'quantity' => \DB::raw("COALESCE(quantity, 0) + {$quantity}"),
            ]
        );

        // Reload to get actual quantity
        $cartItem->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'data' => [
                'product_id' => $productId,
                'quantity' => $cartItem->quantity,
            ],
        ], 201);
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, int $productId): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:999',
        ]);

        $user = $request->user();
        $quantity = (int) $request->input('quantity');

        $cartItem = CartItem::where('user_id', $user->id)
            ->where('product_id', $productId)
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
                'product_id' => $productId,
                'quantity' => $quantity,
            ],
        ]);
    }

    /**
     * Remove product from cart.
     */
    public function destroy(Request $request, int $productId): JsonResponse
    {
        $user = $request->user();

        CartItem::where('user_id', $user->id)
            ->where('product_id', $productId)
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
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1|max:999',
        ]);

        $user = $request->user();
        $items = $request->input('items');

        // Get product IDs from request
        $productIds = array_column($items, 'product_id');

        // Get only valid published product IDs
        $validProductIds = Product::whereIn('id', $productIds)
            ->published()
            ->pluck('id')
            ->toArray();

        // Create a map of product_id => quantity from request
        $itemsMap = [];
        foreach ($items as $item) {
            if (in_array($item['product_id'], $validProductIds)) {
                $itemsMap[$item['product_id']] = $item['quantity'];
            }
        }

        // Merge with existing cart (add quantities for existing items)
        foreach ($itemsMap as $productId => $quantity) {
            $existingItem = CartItem::where('user_id', $user->id)
                ->where('product_id', $productId)
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
                    'product_id' => $productId,
                    'quantity' => $quantity,
                ]);
            }
        }

        // Return updated cart summary
        $cartItems = $user->cartItems()
            ->select('product_id', 'quantity')
            ->get()
            ->keyBy('product_id')
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
