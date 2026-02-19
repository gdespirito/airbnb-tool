<?php

use App\Jobs\ProcessHostexWebhook;
use Illuminate\Support\Facades\Bus;

beforeEach(function () {
    config(['hostex.webhook_secret' => 'test-secret']);
});

test('webhook dispatches job for reservation_created event', function () {
    Bus::fake();

    $this->postJson('/api/webhooks/hostex', [
        'event' => 'reservation_created',
        'reservation_code' => 'RES123',
        'property_id' => 12345,
        'timestamp' => '2026-02-19T05:00:00Z',
    ], [
        'Hostex-Webhook-Secret-Token' => 'test-secret',
    ])->assertSuccessful();

    Bus::assertDispatched(ProcessHostexWebhook::class, function ($job) {
        return $job->reservationCode === 'RES123';
    });
});

test('webhook dispatches job for reservation_updated event', function () {
    Bus::fake();

    $this->postJson('/api/webhooks/hostex', [
        'event' => 'reservation_updated',
        'reservation_code' => 'RES456',
        'stay_code' => 'RES456',
        'property_id' => 12345,
        'timestamp' => '2026-02-19T05:00:00Z',
    ], [
        'Hostex-Webhook-Secret-Token' => 'test-secret',
    ])->assertSuccessful();

    Bus::assertDispatched(ProcessHostexWebhook::class);
});

test('webhook dispatches job for reservation_cancelled event', function () {
    Bus::fake();

    $this->postJson('/api/webhooks/hostex', [
        'event' => 'reservation_cancelled',
        'reservation_code' => 'RES789',
        'property_id' => 12345,
        'timestamp' => '2026-02-19T05:00:00Z',
    ], [
        'Hostex-Webhook-Secret-Token' => 'test-secret',
    ])->assertSuccessful();

    Bus::assertDispatched(ProcessHostexWebhook::class);
});

test('webhook uses stay_code as fallback', function () {
    Bus::fake();

    $this->postJson('/api/webhooks/hostex', [
        'event' => 'reservation_updated',
        'stay_code' => 'STAY-ABC',
        'property_id' => 12345,
        'timestamp' => '2026-02-19T05:00:00Z',
    ], [
        'Hostex-Webhook-Secret-Token' => 'test-secret',
    ])->assertSuccessful();

    Bus::assertDispatched(ProcessHostexWebhook::class, function ($job) {
        return $job->reservationCode === 'STAY-ABC';
    });
});

test('webhook ignores unknown events', function () {
    Bus::fake();

    $this->postJson('/api/webhooks/hostex', [
        'event' => 'property_availability_updated',
        'property_id' => 12345,
    ], [
        'Hostex-Webhook-Secret-Token' => 'test-secret',
    ])->assertSuccessful();

    Bus::assertNotDispatched(ProcessHostexWebhook::class);
});

test('webhook rejects invalid secret token', function () {
    Bus::fake();

    $this->postJson('/api/webhooks/hostex', [
        'event' => 'reservation_created',
        'reservation_code' => 'RES123',
    ], [
        'Hostex-Webhook-Secret-Token' => 'wrong-secret',
    ])->assertUnauthorized();

    Bus::assertNotDispatched(ProcessHostexWebhook::class);
});

test('webhook rejects missing secret token', function () {
    Bus::fake();

    $this->postJson('/api/webhooks/hostex', [
        'event' => 'reservation_created',
        'reservation_code' => 'RES123',
    ])->assertUnauthorized();

    Bus::assertNotDispatched(ProcessHostexWebhook::class);
});
