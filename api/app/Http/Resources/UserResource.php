<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'status' => $this->status ?? 'active',
            'email_verified_at' => $this->email_verified_at,
            'avatar_file_id' => $this->avatar_file_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'role' => $this->whenLoaded('roles', function () {
                $role = $this->roles->first();
                return $role ? new RoleResource($role) : null;
            }),
        ];
    }
}
