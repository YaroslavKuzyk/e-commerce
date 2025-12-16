<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isAdminRoute = str_contains($request->path(), 'admin/');

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'slug' => $this->product->slug,
                    'main_image_file_id' => $this->product->main_image_file_id,
                ];
            }),
            'author_name' => $this->author_name,
            'author_email' => $this->when($isAdminRoute, $this->author_email),
            'rating' => $this->rating,
            'advantages' => $this->advantages,
            'disadvantages' => $this->disadvantages,
            'comment' => $this->comment,
            'youtube_urls' => $this->youtube_urls ?? [],
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(fn($img) => [
                    'id' => $img->id,
                    'file_id' => $img->file_id,
                ]);
            }),
            'status' => $this->when($isAdminRoute, $this->status),
            'notify_on_reply' => $this->when($isAdminRoute, $this->notify_on_reply),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->when($isAdminRoute, $this->updated_at?->toISOString()),
        ];
    }
}
