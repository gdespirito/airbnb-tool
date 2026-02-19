<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'guest_name' => $this->guest_name,
            'guest_phone' => $this->guest_phone,
            'check_in' => $this->check_in?->toDateString(),
            'check_out' => $this->check_out?->toDateString(),
            'status' => $this->status?->value,
            'notes' => $this->notes,
            'property' => new PropertyResource($this->whenLoaded('property')),
        ];
    }
}
