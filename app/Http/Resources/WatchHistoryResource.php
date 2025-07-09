<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WatchHistoryResource extends JsonResource
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
            'video' => new VideoResource($this->whenLoaded('video')),
            'watchedAt' => $this->created_at?->toISOString(),
            'watchedDuration' => $this->watched_duration,
            'totalDuration' => $this->total_duration,
            'completed' => $this->completed,
            'progressPercent' => $this->total_duration > 0 
                ? round(($this->watched_duration / $this->total_duration) * 100, 2)
                : 0
        ];
    }
} 