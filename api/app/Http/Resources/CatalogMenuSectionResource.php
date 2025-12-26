<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogMenuSectionResource extends JsonResource
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
            'catalog_menu_id' => $this->catalog_menu_id,
            'column_index' => $this->column_index,
            'name' => $this->name,
            'link' => $this->link,
            'icon_file_id' => $this->icon_file_id,
            'sort_order' => $this->sort_order,
            'items' => CatalogMenuItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
