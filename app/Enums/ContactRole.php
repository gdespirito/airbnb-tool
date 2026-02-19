<?php

namespace App\Enums;

enum ContactRole: string
{
    case Cleaning = 'cleaning';
    case Handyman = 'handyman';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cleaning => 'Cleaning',
            self::Handyman => 'Handyman',
            self::Other => 'Other',
        };
    }
}
