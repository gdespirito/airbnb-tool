<?php

namespace App\Console\Commands;

use App\Services\HostexClient;
use App\Services\HostexSyncService;
use Illuminate\Console\Command;

class SyncHostexReservationsCommand extends Command
{
    protected $signature = 'hostex:sync';

    protected $description = 'Sync reservations from Hostex API';

    public function handle(HostexSyncService $syncService): int
    {
        $apiKey = config('hostex.api_key');

        if (! $apiKey) {
            $this->error('HOSTEX_API_KEY is not configured.');

            return self::FAILURE;
        }

        $client = new HostexClient($apiKey, config('hostex.base_url'));

        $this->info('Fetching reservations from Hostex...');

        $reservations = $client->reservations([
            'check_in_from' => now()->subDays(30)->toDateString(),
        ]);

        $this->info('Found '.count($reservations).' reservations.');

        $synced = 0;
        $skipped = 0;

        foreach ($reservations as $data) {
            $reservation = $syncService->upsertFromHostexData($data);

            if ($reservation) {
                $synced++;
            } else {
                $skipped++;
            }
        }

        $this->info("Synced: {$synced}, Skipped: {$skipped}");

        return self::SUCCESS;
    }
}
