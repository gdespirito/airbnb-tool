<?php

namespace App\Models;

use App\Enums\ContactRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    /** @use HasFactory<\Database\Factories\ContactFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'role',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'role' => ContactRole::class,
            'metadata' => 'array',
        ];
    }

    public function cleaningTasks(): HasMany
    {
        return $this->hasMany(CleaningTask::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'cleaning_contact_id');
    }
}
