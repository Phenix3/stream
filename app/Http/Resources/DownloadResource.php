<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DownloadResource extends JsonResource
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
            'downloadedAt' => $this->created_at?->toISOString(),
            'downloadUrl' => $this->download_url,
            'fileSize' => $this->file_size,
            'quality' => $this->quality,
            'status' => $this->status,
            'expiresAt' => $this->expires_at?->toISOString(),
        ];
    }
} 