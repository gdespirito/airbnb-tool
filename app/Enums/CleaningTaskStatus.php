<?php

namespace App\Enums;

enum CleaningTaskStatus: string
{
    case Pending = 'pending';
    case Notified = 'notified';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Verified = 'verified';
    case Skipped = 'skipped';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Notified => 'Notified',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Verified => 'Verified',
            self::Skipped => 'Skipped',
        };
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::Completed, self::Verified, self::Skipped => true,
            self::Pending, self::Notified, self::InProgress => false,
        };
    }

    public function isActive(): bool
    {
        return ! $this->isTerminal();
    }

    /**
     * @return array<int, self>
     */
    public static function activeStatuses(): array
    {
        return array_values(array_filter(self::cases(), fn (self $status): bool => $status->isActive()));
    }
}
