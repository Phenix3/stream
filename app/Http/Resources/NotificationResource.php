<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            'message' => $this->message,
            'type' => $this->type,
            'formattedType' => $this->formatted_type,
            'read' => $this->read,
            'readAt' => $this->read_at?->toISOString(),
            'createdAt' => $this->created_at?->toISOString(),
            'data' => $this->data,
            'actionUrl' => $this->action_url,
            'icon' => $this->icon_url,
            'priority' => $this->priority,
            'formattedPriority' => $this->formatted_priority,
            'priorityCssClass' => $this->priority_css_class,
        ];
    }
} 