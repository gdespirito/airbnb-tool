<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'check_in',
        'check_out',
        'status',
        'notes',
        'source',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
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
