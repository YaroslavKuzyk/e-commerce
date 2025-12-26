<?php

namespace App\Http\Resources;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemSettingResource extends JsonResource
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
            'type' => $this->type,
            'name' => $this->name,
            'name_uk' => $this->name_uk,
            'description' => $this->description,
            'description_uk' => $this->description_uk,
            'data' => $this->data,
            'is_active' => $this->is_active,
            'default_structure' => SystemSetting::getDefaultStructure($this->type),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
