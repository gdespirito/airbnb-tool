<?php

namespace App\Mail;

use App\Models\ReservationNote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationNoteCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ReservationNote $note) {}

    public function envelope(): Envelope
    {
        $property = $this->note->reservation->property?->name ?? 'Unknown';
        $guest = $this->note->reservation->guest_name;

        return new Envelope(
            subject: "[{$property}] Nueva nota — {$guest}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.reservation-note-created',
        );
    }
}
