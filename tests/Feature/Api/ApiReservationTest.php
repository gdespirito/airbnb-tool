<?php

use App\Enums\ReservationStatus;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(User::factory()->create(), ['*']);
    $this->property = Property::factory()->create();
});

test('index returns all reservations', function () {
    Reservation::factory()->count(3)->for($this->property)->create();

    $this->getJson('/api/v1/reservations')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('index filters by property_id', function () {
    $other = Property::factory()->create();
    Reservation::factory()->count(2)->for($this->property)->create();
    Reservation::factory()->for($other)->create();

    $response = $this->getJson("/api/v1/reservations?property_id={$this->property->id}")
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(2);
});

test('index filters by status', function () {
    Reservation::factory()->for($this->property)->create(['status' => ReservationStatus::Confirmed]);
    Reservation::factory()->for($this->property)->create(['status' => ReservationStatus::Cancelled]);

    $response = $this->getJson('/api/v1/reservations?status=confirmed')->assertSuccessful();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.status'))->toBe('confirmed');
});

test('index filters upcoming reservations', function () {
    Reservation::factory()->for($this->property)->create([
        'check_in' => now()->addDays(5),
        'check_out' => now()->addDays(8),
    ]);
    Reservation::factory()->for($this->property)->create([
        'check_in' => now()->subDays(10),
        'check_out' => now()->subDays(7),
    ]);

    $response = $this->getJson('/api/v1/reservations?upcoming=1')->assertSuccessful();

    expect($response->json('data'))->toHaveCount(1);
});

test('index filters by check_in date range', function () {
    Reservation::factory()->for($this->property)->create([
        'check_in' => '2026-03-01',
        'check_out' => '2026-03-05',
    ]);
    Reservation::factory()->for($this->property)->create([
        'check_in' => '2026-04-01',
        'check_out' => '2026-04-05',
    ]);

    $response = $this->getJson('/api/v1/reservations?check_in_from=2026-03-01&check_in_to=2026-03-31')
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.check_in'))->toBe('2026-03-01');
});

test('show returns reservation with nested property', function () {
    $reservation = Reservation::factory()->for($this->property)->create();

    $response = $this->getJson("/api/v1/reservations/{$reservation->id}")->assertSuccessful();

    expect($response->json('data.id'))->toBe($reservation->id)
        ->and($response->json('data.property.id'))->toBe($this->property->id);
});
