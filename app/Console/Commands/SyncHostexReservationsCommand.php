<?php

namespace App\Console\Commands;

use App\Services\HostexClient;
use App\Services\HostexSyncService;
use Illuminate\Console\Command;

class SyncHostexReservationsCommand extends Command
{
    protected $signature = 'hostex:sync {--all : Fetch all reservations instead of only upcoming}';

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

        $filters = $this->option('all') ? [] : [
            'start_check_out_date' => now()->toDateString(),
        ];

        $reservations = $client->reservations($filters);

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
