<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationNoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reservation_id' => $this->reservation_id,
            'content' => $this->content,
            'from_agent' => $this->from_agent,
            'needs_response' => $this->needs_response,
            'responded_at' => $this->responded_at?->toIso8601String(),
            'reservation' => new ReservationResource($this->whenLoaded('reservation')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
