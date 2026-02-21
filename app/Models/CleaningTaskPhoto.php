<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CleaningTaskPhoto extends Model
{
    protected $fillable = [
        'cleaning_task_id',
        'file_path',
        'disk',
        'original_filename',
        'mime_type',
        'file_size',
    ];

    public function cleaningTask(): BelongsTo
    {
        return $this->belongsTo(CleaningTask::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->file_path);
    }
}
