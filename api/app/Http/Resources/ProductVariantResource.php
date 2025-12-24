<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'slug' => $this->slug,
            'name' => $this->name,
            'price' => $this->price,
            'stock' => $this->stock,
            'status' => $this->status->value,
            'is_default' => $this->is_default,
            'override_pricing' => $this->override_pricing,
            'discount_price' => $this->discount_price,
            'discount_percent' => $this->discount_percent,
            'discount_starts_at' => $this->discount_starts_at?->toISOString(),
            'discount_ends_at' => $this->discount_ends_at?->toISOString(),
            'is_clearance' => $this->is_clearance,
            'clearance_price' => $this->clearance_price,
            'clearance_reason' => $this->clearance_reason,
            'current_price' => $this->current_price,
            'discount_percentage' => $this->discount_percentage,
            'attribute_values' => AttributeValueResource::collection($this->whenLoaded('attributeValues')),
            'images' => ProductVariantImageResource::collection($this->whenLoaded('images')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
