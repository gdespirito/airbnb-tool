<?php

namespace App\Jobs;

use App\Models\ReservationNote;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifyAgentResponse implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ReservationNote $note,
    ) {}

    public function handle(): void
    {
        $webhookUrl = config('services.openclaw.webhook_url');

        if (! $webhookUrl) {
            Log::warning('NotifyAgentResponse: OPENCLAW_WEBHOOK_URL not configured');

            return;
        }

        $parent = $this->note->parent;

        $response = Http::post($webhookUrl.'/agent-response', [
            'note_id' => $parent?->id ?? $this->note->id,
            'from_agent' => $parent?->from_agent ?? $this->note->from_agent,
            'original_content' => $parent?->content,
            'response' => $this->note->content,
            'guest_name' => $this->note->reservation->guest_name,
            'property_name' => $this->note->reservation->property->name,
        ]);

        if ($response->successful()) {
            $parentNote = $parent ?? $this->note;
            $parentNote->response_notified = true;
            $parentNote->save();
        } else {
            Log::error('NotifyAgentResponse: webhook failed', [
                'note_id' => $this->note->id,
                'status' => $response->status(),
            ]);
        }
    }
}
