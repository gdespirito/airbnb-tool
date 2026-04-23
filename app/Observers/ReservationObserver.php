<?php

namespace App\Observers;

use App\Actions\CleaningTasks\SkipStaleCleaningTasks;
use App\Enums\CleaningType;
use App\Enums\ReservationStatus;
use App\Models\Reservation;

class ReservationObserver
{
    public function __construct(
        private readonly SkipStaleCleaningTasks $skipStaleCleaningTasks,
    ) {}

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
        if ($reservation->wasChanged('check_out')) {
            $reservation->cleaningTask?->update([
                'scheduled_date' => $reservation->check_out,
            ]);
        }

        if (! $reservation->wasChanged('status')) {
            return;
        }

        match ($reservation->status) {
            ReservationStatus::CheckedIn => $this->skipStaleCleaningTasks->forProperty(
                $reservation->property_id,
                $reservation->check_in,
            ),
            ReservationStatus::Cancelled => $this->skipStaleCleaningTasks->forCancelledReservation($reservation),
            default => null,
        };
    }
}
