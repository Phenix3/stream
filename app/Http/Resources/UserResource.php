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
            'phone' => $this->phone,
            'avatar' => $this->avatar ? url($this->avatar) : null,
            'accountId' => $this->account_id,
            'emailVerified' => $this->hasVerifiedEmail(),
            'phoneVerified' => $this->hasVerifiedPhone(),
            'joinDate' => $this->created_at?->toISOString(),
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'statistics' => $this->statistics,
        ];
    }
} 