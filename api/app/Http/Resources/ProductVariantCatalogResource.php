<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for displaying product variants as separate items in catalog.
 * Combines variant data with parent product data for catalog display.
 */
class ProductVariantCatalogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = $this->product;
        $primaryImage = $this->images->where('is_primary', true)->first();
        $mainImageFileId = $primaryImage?->file_id ?? $product?->main_image_file_id;

        // Build variant name (product name + attribute values)
        $variantName = $product?->name ?? '';
        if ($this->attributeValues && $this->attributeValues->isNotEmpty()) {
            $attrValues = $this->attributeValues->pluck('value')->implode(' / ');
            $variantName = $product?->name . ' (' . $attrValues . ')';
        }

        return [
            // Variant ID for cart/comparison operations
            'id' => $this->id,
            'variant_id' => $this->id,
            'product_id' => $this->product_id,

            // Names
            'name' => $variantName,
            'product_name' => $product?->name,
            'variant_name' => $this->name,

            // Slugs for URL building
            'slug' => $product?->slug,
            'variant_slug' => $this->slug,

            // SKU
            'sku' => $this->sku,

            // Descriptions from product
            'short_description' => $product?->short_description,
            'description' => $product?->description,

            // Category & Brand from product
            'category_id' => $product?->category_id,
            'category' => new ProductCategoryResource($this->whenLoaded('product', function () {
                return $this->product->category;
            })),
            'brand_id' => $product?->brand_id,
            'brand' => new ProductBrandResource($this->whenLoaded('product', function () {
                return $this->product->brand;
            })),

            // Pricing - variant's price
            'base_price' => $this->price,
            'current_price' => $this->current_price,
            'discount_percentage' => $this->discount_percentage,

            // Discount info (effective - from variant or inherited from product)
            'has_active_discount' => $this->hasActiveDiscount(),
            'is_clearance' => $this->getEffectiveIsClearance(),

            // Stock
            'stock' => $this->stock,
            'in_stock' => $this->stock > 0,

            // Status
            'status' => $this->status->value,

            // Images - variant's main image or product's main image
            'main_image_file_id' => $mainImageFileId,

            // Attribute values for this variant (e.g., color, size)
            'attribute_values' => AttributeValueResource::collection($this->whenLoaded('attributeValues')),

            // Variant images
            'images' => ProductVariantImageResource::collection($this->whenLoaded('images')),

            // Rating & Reviews (from parent product)
            'average_rating' => $product?->average_rating ?? 0,
            'reviews_count' => $product?->reviews_count ?? 0,

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
