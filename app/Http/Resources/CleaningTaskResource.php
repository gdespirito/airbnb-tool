<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CleaningTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status?->value,
            'cleaning_type' => $this->cleaning_type?->value,
            'scheduled_date' => $this->scheduled_date?->toDateString(),
            'cleaning_fee' => $this->cleaning_fee,
            'notes' => $this->notes,
            'estimated_arrival_time' => $this->estimated_arrival_time,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'assigned_to' => $this->assigned_to,
            'assigned_phone' => $this->assigned_phone,
            'property' => new PropertyResource($this->whenLoaded('property')),
            'reservation' => new ReservationResource($this->whenLoaded('reservation')),
            'contact' => new ContactResource($this->whenLoaded('contact')),
            'photos' => CleaningTaskPhotoResource::collection($this->whenLoaded('photos')),
            'has_same_day_checkin' => $this->when($this->getAttribute('has_same_day_checkin') !== null, $this->getAttribute('has_same_day_checkin')),
            'next_guest_name' => $this->when($this->getAttribute('next_guest_name') !== null, $this->getAttribute('next_guest_name')),
            'checkin_time' => $this->when($this->getAttribute('checkin_time') !== null, $this->getAttribute('checkin_time')),
        ];
    }
}
