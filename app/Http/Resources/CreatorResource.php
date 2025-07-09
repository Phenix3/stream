<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreatorResource extends JsonResource
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
            'avatar' => $this->avatar_url,
            'subscriberCount' => $this->subscriber_count,
            'description' => $this->description,
            'verified' => $this->verified,
            'videosCount' => $this->when(
                $this->relationLoaded('videos'),
                $this->videos_count ?? $this->videos->count()
            ),
            'totalViews' => $this->when(
                $this->relationLoaded('videos'),
                $this->total_views
            ),
            'formattedSubscriberCount' => $this->formatted_subscriber_count,
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
} 