<?php

namespace App\Models;

use App\Enums\CleaningTaskStatus;
use App\Enums\CleaningType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CleaningTask extends Model
{
    /** @use HasFactory<\Database\Factories\CleaningTaskFactory> */
    use HasFactory;

    protected $fillable = [
        'property_id',
        'reservation_id',
        'contact_id',
        'status',
        'cleaning_type',
        'cleaning_fee',
        'scheduled_date',
        'assigned_to',
        'assigned_phone',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => CleaningTaskStatus::class,
            'cleaning_type' => CleaningType::class,
            'scheduled_date' => 'date',
            'metadata' => 'array',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', CleaningTaskStatus::Pending);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('scheduled_date', '>=', now())
            ->orderBy('scheduled_date');
    }

    public function scopeForProperty(Builder $query, int $propertyId): Builder
    {
        return $query->where('property_id', $propertyId);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', CleaningTaskStatus::Completed);
    }
}
