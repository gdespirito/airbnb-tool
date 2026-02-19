<?php

use App\Jobs\ProcessHostexWebhook;
use Illuminate\Support\Facades\Bus;

beforeEach(function () {
    config(['hostex.webhook_secret' => 'test-secret']);
});

test('webhook dispatches job for reservation.created event', function () {
    Bus::fake();

    $payload = [
        'event' => 'reservation.created',
        'data' => [
            'reservation_code' => 'RES123',
            'property_id' => 'prop-1',
            'guest_name' => 'John Doe',
            'check_in' => '2026-03-01',
            'check_out' => '2026-03-05',
            'status' => 'accepted',
        ],
    ];

    $this->postJson('/api/webhooks/hostex', $payload, [
        'Hostex-Webhook-Secret-Token' => 'test-secret',
    ])->assertSuccessful();

    Bus::assertDispatched(ProcessHostexWebhook::class, function ($job) {
        return $job->data['reservation_code'] === 'RES123';
    });
});

test('webhook dispatches job for reservation.updated event', function () {
    Bus::fake();

    $this->postJson('/api/webhooks/hostex', [
        'event' => 'reservation.updated',
        'data' => ['reservation_code' => 'RES456', 'property_id' => 'prop-1'],
    ], [
        'Hostex-Webhook-Secret-Token' => 'test-secret',
    ])->assertSuccessful();

    Bus::assertDispatched(ProcessHostexWebhook::class);
});

test('webhook dispatches job for reservation.cancelled event', function () {
    Bus::fake();

    $this->postJson('/api/webhooks/hostex', [
        'event' => 'reservation.cancelled',
        'data' => ['reservation_code' => 'RES789', 'property_id' => 'prop-1'],
    ], [
        'Hostex-Webhook-Secret-Token' => 'test-secret',
    ])->assertSuccessful();

    Bus::assertDispatched(ProcessHostexWebhook::class);
});

test('webhook ignores unknown events', function () {
    Bus::fake();

    $this->postJson('/api/webhooks/hostex', [
        'event' => 'property.updated',
        'data' => ['id' => 'prop-1'],
    ], [
        'Hostex-Webhook-Secret-Token' => 'test-secret',
    ])->assertSuccessful();

    Bus::assertNotDispatched(ProcessHostexWebhook::class);
});

test('webhook rejects invalid secret token', function () {
    Bus::fake();

    $this->postJson('/api/webhooks/hostex', [
        'event' => 'reservation.created',
        'data' => ['reservation_code' => 'RES123'],
    ], [
        'Hostex-Webhook-Secret-Token' => 'wrong-secret',
    ])->assertUnauthorized();

    Bus::assertNotDispatched(ProcessHostexWebhook::class);
});

test('webhook rejects missing secret token', function () {
    Bus::fake();

    $this->postJson('/api/webhooks/hostex', [
        'event' => 'reservation.created',
        'data' => ['reservation_code' => 'RES123'],
    ])->assertUnauthorized();

    Bus::assertNotDispatched(ProcessHostexWebhook::class);
});
