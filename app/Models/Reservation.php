<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'airbnb_reservation_id',
        'guest_name',
        'guest_phone',
        'guest_email',
        'number_of_guests',
        'number_of_adults',
        'number_of_children',
        'number_of_infants',
        'number_of_pets',
        'check_in',
        'check_out',
        'status',
        'notes',
        'source',
        'channel_type',
        'booked_at',
        'cancelled_at',
        'total_price',
        'currency',
        'check_in_time',
        'check_out_time',
        'lock_code',
        'hostex_conversation_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'booked_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'total_price' => 'decimal:2',
            'status' => ReservationStatus::class,
            'metadata' => 'array',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function cleaningTask(): HasOne
    {
        return $this->hasOne(CleaningTask::class);
    }

    public function reservationNotes(): HasMany
    {
        return $this->hasMany(ReservationNote::class);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('check_in', '>=', now())
            ->orderBy('check_in');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ReservationStatus::CheckedIn);
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('check_in', $date)
            ->orWhereDate('check_out', $date);
    }

    public function isSameDayTurnover(): bool
    {
        return Reservation::query()
            ->where('property_id', $this->property_id)
            ->whereDate('check_in', $this->check_out)
            ->where('id', '!=', $this->id)
            ->exists();
    }
}
