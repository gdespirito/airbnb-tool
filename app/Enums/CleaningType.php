<?php

namespace App\Enums;

enum CleaningType: string
{
    case Checkout = 'checkout';
    case DeepClean = 'deep_clean';
    case TouchUp = 'touch_up';

    public function label(): string
    {
        return match ($this) {
            self::Checkout => 'Checkout',
            self::DeepClean => 'Deep Clean',
            self::TouchUp => 'Touch Up',
        };
    }
}
