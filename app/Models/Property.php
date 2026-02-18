<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    /** @use HasFactory<\Database\Factories\PropertyFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'airbnb_url',
        'airbnb_listing_id',
        'ical_url',
        'location',
        'latitude',
        'longitude',
        'checkin_time',
        'checkout_time',
        'cleaning_contact_name',
        'cleaning_contact_phone',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function upcomingReservations(): HasMany
    {
        return $this->hasMany(Reservation::class)
            ->whereDate('check_in', '>=', now())
            ->orderBy('check_in');
    }
}
