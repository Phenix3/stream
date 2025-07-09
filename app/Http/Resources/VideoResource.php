<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'thumbnail' => $this->thumbnail_url,
            'content_url' => $this->content_url,
            'creator' => new CreatorResource($this->whenLoaded('creator')),
            'views' => $this->views,
            'duration' => $this->duration,
            'genre' => GenreResource::collection($this->whenLoaded('genres')),
            'actors' => ActorResource::collection($this->whenLoaded('actors')),
            'rating' => (float) $this->rating,
            'visibility' => $this->visibility,
            'season' => $this->season,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
} 