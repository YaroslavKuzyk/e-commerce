<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryMethodResource extends JsonResource
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
            'has_api' => $this->has_api,
            'api_config' => $this->api_config,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'payment_methods' => PaymentMethodResource::collection($this->whenLoaded('paymentMethods')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
