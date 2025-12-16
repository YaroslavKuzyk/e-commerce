<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'category' => new ProductCategoryResource($this->whenLoaded('category')),
            'brand_id' => $this->brand_id,
            'brand' => new ProductBrandResource($this->whenLoaded('brand')),
            'status' => $this->status->value,
            'base_price' => $this->base_price,
            'discount_price' => $this->discount_price,
            'discount_percent' => $this->discount_percent,
            'discount_starts_at' => $this->discount_starts_at?->toISOString(),
            'discount_ends_at' => $this->discount_ends_at?->toISOString(),
            'is_clearance' => $this->is_clearance,
            'clearance_price' => $this->clearance_price,
            'clearance_reason' => $this->clearance_reason,
            'current_price' => $this->current_price,
            'discount_percentage' => $this->discount_percentage,
            'average_rating' => $this->average_rating,
            'reviews_count' => $this->reviews_count,
            'main_image_file_id' => $this->main_image_file_id,
            'main_image' => new FileResource($this->whenLoaded('mainImage')),
            'attributes' => AttributeResource::collection($this->whenLoaded('attributes')),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'specifications' => ProductSpecificationResource::collection($this->whenLoaded('specifications')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
