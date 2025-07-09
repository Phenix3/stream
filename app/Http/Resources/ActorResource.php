<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActorResource extends JsonResource
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
            'profileImage' => $this->profile_image_url,
            'biography' => $this->biography,
            'filmography' => $this->filmography,
            'filmographyString' => $this->filmography_string,
            'videosCount' => $this->when(
                $this->relationLoaded('videos'),
                $this->videos_count ?? $this->videos->count()
            ),
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
} 