<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'token' => $this->token,
            'refreshToken' => $this->when(isset($this->refresh_token), $this->refresh_token),
            'expiresAt' => $this->expires_at?->toISOString(),
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
} 