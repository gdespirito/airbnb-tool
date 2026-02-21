<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservationNote extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationNoteFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reservation_id',
        'content',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
