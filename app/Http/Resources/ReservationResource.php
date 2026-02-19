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
            'number_of_adults' => $this->number_of_adults,
            'number_of_children' => $this->number_of_children,
            'number_of_infants' => $this->number_of_infants,
            'number_of_pets' => $this->number_of_pets,
            'check_in' => $this->check_in?->toDateString(),
            'check_out' => $this->check_out?->toDateString(),
            'status' => $this->status?->value,
            'notes' => $this->notes,
            'source' => $this->source,
            'channel_type' => $this->channel_type,
            'booked_at' => $this->booked_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'total_price' => $this->total_price,
            'currency' => $this->currency,
            'check_in_time' => $this->check_in_time,
            'check_out_time' => $this->check_out_time,
            'lock_code' => $this->lock_code,
            'hostex_conversation_id' => $this->hostex_conversation_id,
            'property' => new PropertyResource($this->whenLoaded('property')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
