<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GenreResource extends JsonResource
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
            'slug' => $this->slug,
            'description' => $this->description,
            'color' => $this->color,
            'icon' => $this->icon_url,
            'videosCount' => $this->when(
                $this->relationLoaded('videos'),
                $this->videos_count ?? $this->videos->count()
            ),
            'colorStyle' => $this->color_style,
        ];
    }
} 