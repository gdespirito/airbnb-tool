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
            'property' => new PropertyResource($this->whenLoaded('property')),
            'reservation' => new ReservationResource($this->whenLoaded('reservation')),
            'contact' => new ContactResource($this->whenLoaded('contact')),
        ];
    }
}
