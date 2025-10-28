<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
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
            'name_uk' => $this->name_uk,
            'code' => $this->code,
            'description' => $this->description,
            'description_uk' => $this->description_uk,
            'type' => $this->type,
            'provider' => $this->provider,
            'provider_config' => $this->provider_config,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'pivot_is_active' => $this->whenPivotLoaded('delivery_payment_method', function () {
                return $this->pivot->is_active;
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
