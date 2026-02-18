<?php

namespace App\Enums;

enum CleaningTaskStatus: string
{
    case Pending = 'pending';
    case Notified = 'notified';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Verified = 'verified';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Notified => 'Notified',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Verified => 'Verified',
        };
    }
}
