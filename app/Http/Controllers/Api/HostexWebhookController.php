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

        if (in_array($event, ['reservation.created', 'reservation.updated', 'reservation.cancelled'])) {
            ProcessHostexWebhook::dispatch($data['data'] ?? $data);
        }

        return response()->json(['ok' => true]);
    }
}
