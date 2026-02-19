<?php

namespace App\Jobs;

use App\Services\HostexSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessHostexWebhook implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public readonly array $data,
    ) {}

    public function handle(HostexSyncService $service): void
    {
        $service->upsertFromHostexData($this->data);
    }
}
