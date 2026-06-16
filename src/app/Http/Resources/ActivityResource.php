<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
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
            'user_id' => $this->user_id,
            'title' => $this->title,
            'notes' => $this->notes,
            'location' => $this->location,
            'role' => $this->role,
            'invitations' => InvitationResource::collection($this->whenLoaded('invitations')),
            'starts_at' => $this->starts_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function withResponse(Request $request, $response)
    {
        if ($this->wasRecentlyCreated) {
            $response->setStatusCode(201);
        }
    }
}