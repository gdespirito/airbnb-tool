<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HostexClient
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function properties(): array
    {
        return $this->paginatedGet('/properties');
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function reservations(array $filters = []): array
    {
        return $this->paginatedGet('/reservations', $filters);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function reservation(string $reservationCode): ?array
    {
        $results = $this->paginatedGet('/reservations', [
            'reservation_code' => $reservationCode,
        ]);

        return $results[0] ?? null;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<int, array<string, mixed>>
     */
    private function paginatedGet(string $endpoint, array $params = []): array
    {
        $results = [];
        $offset = 0;
        $limit = 50;

        do {
            $response = $this->client()->get($endpoint, array_merge($params, [
                'offset' => $offset,
                'limit' => $limit,
            ]));

            if ($response->failed()) {
                Log::error('Hostex API request failed', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                break;
            }

            $data = $response->json('data', []);
            $results = array_merge($results, $data);
            $offset += $limit;
        } while (count($data) === $limit);

        return $results;
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders(['Hostex-Access-Token' => $this->apiKey])
            ->acceptJson()
            ->timeout(30);
    }
}
