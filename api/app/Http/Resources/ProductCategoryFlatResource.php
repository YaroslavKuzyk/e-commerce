<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCategoryFlatResource extends JsonResource
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
            'subtitle' => $this->subtitle,
            'slug' => $this->slug,
            'status' => $this->status->value,
            'body_description' => $this->body_description,
            'logo_file_id' => $this->logo_file_id,
            'menu_image_file_id' => $this->menu_image_file_id,
            'subcategories_count' => $this->whenCounted('subcategories'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
