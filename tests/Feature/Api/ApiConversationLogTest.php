<?php

use App\Models\ConversationLog;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(User::factory()->create(), ['*']);
    $this->property = Property::factory()->create();
    $this->reservation = Reservation::factory()->for($this->property)->create();
});

test('index returns conversation logs for a reservation', function () {
    ConversationLog::factory()->count(3)->for($this->reservation)->create();

    $other = Reservation::factory()->for($this->property)->create();
    ConversationLog::factory()->count(2)->for($other)->create();

    $response = $this->getJson("/api/v1/reservations/{$this->reservation->id}/conversation-logs")
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(3);
});

test('store creates a conversation log', function () {
    $response = $this->postJson("/api/v1/reservations/{$this->reservation->id}/conversation-logs", [
        'from_agent' => 'alma',
        'contact_name' => 'John Doe',
        'contact_role' => 'guest',
        'channel' => 'whatsapp',
        'summary' => 'Guest asked about check-in time',
    ])->assertCreated();

    expect($response->json('data.from_agent'))->toBe('alma')
        ->and($response->json('data.contact_name'))->toBe('John Doe')
        ->and($response->json('data.contact_role'))->toBe('guest')
        ->and($response->json('data.summary'))->toBe('Guest asked about check-in time')
        ->and($response->json('data.reservation_id'))->toBe($this->reservation->id);

    $this->assertDatabaseHas('conversation_logs', [
        'reservation_id' => $this->reservation->id,
        'from_agent' => 'alma',
        'contact_name' => 'John Doe',
    ]);
});

test('store validates required fields', function () {
    $this->postJson("/api/v1/reservations/{$this->reservation->id}/conversation-logs", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['from_agent', 'contact_name', 'contact_role', 'summary']);
});

test('store validates from_agent must be valid agent name', function () {
    $this->postJson("/api/v1/reservations/{$this->reservation->id}/conversation-logs", [
        'from_agent' => 'invalid_agent',
        'contact_name' => 'John Doe',
        'contact_role' => 'guest',
        'summary' => 'Test',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['from_agent']);
});

test('store validates contact_role must be valid', function () {
    $this->postJson("/api/v1/reservations/{$this->reservation->id}/conversation-logs", [
        'from_agent' => 'alma',
        'contact_name' => 'John Doe',
        'contact_role' => 'invalid_role',
        'summary' => 'Test',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['contact_role']);
});

test('store accepts messages array', function () {
    $messages = [
        ['from' => 'Guest', 'text' => 'Hello!', 'at' => '2026-03-08 10:00:00'],
        ['from' => 'Alma', 'text' => 'Hi! How can I help?', 'at' => '2026-03-08 10:01:00'],
    ];

    $response = $this->postJson("/api/v1/reservations/{$this->reservation->id}/conversation-logs", [
        'from_agent' => 'alma',
        'contact_name' => 'John Doe',
        'contact_role' => 'guest',
        'summary' => 'Initial greeting',
        'messages' => $messages,
    ])->assertCreated();

    expect($response->json('data.messages'))->toHaveCount(2)
        ->and($response->json('data.messages.0.from'))->toBe('Guest');
});

test('show returns a single conversation log', function () {
    $log = ConversationLog::factory()->for($this->reservation)->create();

    $response = $this->getJson("/api/v1/conversation-logs/{$log->id}")
        ->assertSuccessful();

    expect($response->json('data.id'))->toBe($log->id)
        ->and($response->json('data.summary'))->toBe($log->summary);
});

test('store defaults channel to whatsapp when not provided', function () {
    $response = $this->postJson("/api/v1/reservations/{$this->reservation->id}/conversation-logs", [
        'from_agent' => 'tita',
        'contact_name' => 'Eliene',
        'contact_role' => 'cleaner',
        'summary' => 'Confirmed cleaning schedule',
    ])->assertCreated();

    expect($response->json('data.channel'))->toBe('whatsapp');
});
