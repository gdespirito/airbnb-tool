<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Confirmed = 'confirmed';
    case CheckedIn = 'checked_in';
    case CheckedOut = 'checked_out';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Confirmed => 'Confirmed',
            self::CheckedIn => 'Checked In',
            self::CheckedOut => 'Checked Out',
            self::Cancelled => 'Cancelled',
        };
    }
}
