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

test('index accepts upcoming=true string', function () {
    Reservation::factory()->for($this->property)->create([
        'check_in' => now()->addDays(3),
        'check_out' => now()->addDays(6),
    ]);

    $this->getJson('/api/v1/reservations?upcoming=true')->assertSuccessful();
    $this->getJson('/api/v1/reservations?upcoming=false')->assertSuccessful();
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

test('store creates a reservation', function () {
    $response = $this->postJson('/api/v1/reservations', [
        'property_id' => $this->property->id,
        'guest_name' => 'Ana Ruiz',
        'guest_email' => 'ana@example.com',
        'guest_phone' => '+56912345678',
        'number_of_guests' => 2,
        'check_in' => '2026-05-01',
        'check_out' => '2026-05-05',
        'status' => 'confirmed',
    ])->assertCreated();

    expect($response->json('data.guest_name'))->toBe('Ana Ruiz')
        ->and($response->json('data.check_in'))->toBe('2026-05-01')
        ->and($response->json('data.status'))->toBe('confirmed')
        ->and($response->json('data.property.id'))->toBe($this->property->id);

    $this->assertDatabaseHas('reservations', ['guest_name' => 'Ana Ruiz']);
});

test('store requires guest_name, property_id, check_in, check_out', function () {
    $this->postJson('/api/v1/reservations', [])->assertUnprocessable()
        ->assertJsonValidationErrors(['property_id', 'guest_name', 'check_in', 'check_out']);
});

test('store validates check_out is after check_in', function () {
    $this->postJson('/api/v1/reservations', [
        'property_id' => $this->property->id,
        'guest_name' => 'Test Guest',
        'check_in' => '2026-05-05',
        'check_out' => '2026-05-01',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['check_out']);
});

test('update modifies an existing reservation', function () {
    $reservation = Reservation::factory()->for($this->property)->create(['guest_name' => 'Old Name']);

    $response = $this->putJson("/api/v1/reservations/{$reservation->id}", [
        'guest_name' => 'New Name',
        'status' => 'checked_in',
    ])->assertSuccessful();

    expect($response->json('data.guest_name'))->toBe('New Name')
        ->and($response->json('data.status'))->toBe('checked_in');

    $this->assertDatabaseHas('reservations', ['id' => $reservation->id, 'guest_name' => 'New Name']);
});

test('update validates status enum', function () {
    $reservation = Reservation::factory()->for($this->property)->create();

    $this->putJson("/api/v1/reservations/{$reservation->id}", ['status' => 'invalid_status'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

test('destroy soft deletes a reservation', function () {
    $reservation = Reservation::factory()->for($this->property)->create();

    $this->deleteJson("/api/v1/reservations/{$reservation->id}")->assertNoContent();

    $this->assertSoftDeleted('reservations', ['id' => $reservation->id]);
});

test('soft deleted reservation is excluded from index', function () {
    $reservation = Reservation::factory()->for($this->property)->create();
    $reservation->delete();

    $this->getJson('/api/v1/reservations')
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

test('soft deleted reservation returns 404 on show', function () {
    $reservation = Reservation::factory()->for($this->property)->create();
    $reservation->delete();

    $this->getJson("/api/v1/reservations/{$reservation->id}")->assertNotFound();
});
