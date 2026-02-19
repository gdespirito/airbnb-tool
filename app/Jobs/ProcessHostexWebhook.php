<?php

namespace App\Jobs;

use App\Services\HostexClient;
use App\Services\HostexSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessHostexWebhook implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $reservationCode,
    ) {}

    public function handle(HostexSyncService $syncService): void
    {
        $client = new HostexClient(
            config('hostex.api_key'),
            config('hostex.base_url'),
        );

        $data = $client->reservation($this->reservationCode);

        if (! $data) {
            Log::warning('Hostex webhook: reservation not found via API', [
                'reservation_code' => $this->reservationCode,
            ]);

            return;
        }

        $syncService->upsertFromHostexData($data);
    }
}
