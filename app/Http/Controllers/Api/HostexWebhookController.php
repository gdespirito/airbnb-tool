<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessHostexWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HostexWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        Log::info('Hostex webhook received', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ]);

        $secret = config('hostex.webhook_secret');

        if ($secret && $request->header('Hostex-Webhook-Secret-Token') !== $secret) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->all();
        $event = $data['event'] ?? null;

        if (in_array($event, ['reservation_created', 'reservation_updated', 'reservation_cancelled'])) {
            $reservationCode = $data['reservation_code'] ?? $data['stay_code'] ?? null;

            if ($reservationCode) {
                ProcessHostexWebhook::dispatch($reservationCode);
            }
        }

        return response()->json(['ok' => true]);
    }
}
