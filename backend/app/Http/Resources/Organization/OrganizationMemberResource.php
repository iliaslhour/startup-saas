<?php

namespace App\Http\Resources\Organization;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->when($this->pivot?->role_id, function () {
                return [
                    'id' => $this->pivot->role_id,
                    'name' => $this->pivot->role?->name,
                    'slug' => $this->pivot->role?->slug,
                ];
            }),
            'joined_at' => $this->pivot?->created_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}