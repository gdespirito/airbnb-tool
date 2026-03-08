<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reservation_id' => $this->reservation_id,
            'from_agent' => $this->from_agent,
            'contact_name' => $this->contact_name,
            'contact_role' => $this->contact_role,
            'channel' => $this->channel,
            'summary' => $this->summary,
            'messages' => $this->messages,
            'reservation' => new ReservationResource($this->whenLoaded('reservation')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
