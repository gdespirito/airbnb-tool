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
            'airbnb_reservation_id' => $this->airbnb_reservation_id,
            'guest_name' => $this->guest_name,
            'guest_phone' => $this->guest_phone,
            'guest_email' => $this->guest_email,
            'number_of_guests' => $this->number_of_guests,
            'check_in' => $this->check_in?->toDateString(),
            'check_out' => $this->check_out?->toDateString(),
            'status' => $this->status?->value,
            'notes' => $this->notes,
            'source' => $this->source,
            'property' => new PropertyResource($this->whenLoaded('property')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
