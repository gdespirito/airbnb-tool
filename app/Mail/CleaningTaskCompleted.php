<?php

namespace App\Mail;

use App\Enums\ReservationStatus;
use App\Models\CleaningTask;
use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CleaningTaskCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public bool $hasSameDayCheckin;

    public ?string $nextGuestName;

    public function __construct(public CleaningTask $task)
    {
        $nextReservation = Reservation::query()
            ->where('property_id', $task->property_id)
            ->whereDate('check_in', $task->scheduled_date)
            ->whereNot('status', ReservationStatus::Cancelled)
            ->first();

        $this->hasSameDayCheckin = $nextReservation !== null;
        $this->nextGuestName = $nextReservation?->guest_name;
    }

    public function envelope(): Envelope
    {
        $property = $this->task->property?->name ?? 'Unknown';
        $cleaner = $this->task->assigned_to ?? 'N/A';

        return new Envelope(
            subject: "[{$property}] Limpieza completada — {$cleaner}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.cleaning-task-completed',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return $this->task->photos->map(function ($photo) {
            return Attachment::fromStorageDisk($photo->disk, $photo->file_path)
                ->as($photo->original_filename)
                ->withMime($photo->mime_type);
        })->all();
    }
}
