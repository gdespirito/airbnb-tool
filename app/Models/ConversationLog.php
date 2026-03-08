<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'from_agent',
        'contact_name',
        'contact_role',
        'channel',
        'summary',
        'messages',
    ];

    protected function casts(): array
    {
        return [
            'messages' => 'array',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
