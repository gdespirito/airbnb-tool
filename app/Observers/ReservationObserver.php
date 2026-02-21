<?php

namespace App\Observers;

use App\Enums\CleaningType;
use App\Enums\ReservationStatus;
use App\Models\Reservation;

class ReservationObserver
{
    public function created(Reservation $reservation): void
    {
        if ($reservation->status !== ReservationStatus::Confirmed) {
            return;
        }

        $property = $reservation->property;

        $reservation->cleaningTask()->create([
            'property_id' => $reservation->property_id,
            'cleaning_type' => CleaningType::Checkout,
            'scheduled_date' => $reservation->check_out,
            'cleaning_fee' => $property->metadata['cleaning_fee'] ?? null,
            'contact_id' => $property->cleaning_contact_id,
        ]);
    }

    public function updated(Reservation $reservation): void
    {
        if (! $reservation->wasChanged('check_out')) {
            return;
        }

        $reservation->cleaningTask?->update([
            'scheduled_date' => $reservation->check_out,
        ]);
    }
}
