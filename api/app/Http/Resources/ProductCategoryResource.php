<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCategoryResource extends JsonResource
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
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status->value,
            'body_description' => $this->body_description,
            'logo_file_id' => $this->logo_file_id,
            'menu_image_file_id' => $this->menu_image_file_id,
            'subcategories_count' => $this->whenCounted('subcategories'),
            'subcategories' => ProductCategoryResource::collection($this->whenLoaded('subcategories')),
            'parent' => new ProductCategoryResource($this->whenLoaded('parent')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
