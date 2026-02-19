<?php

use App\Enums\ReservationStatus;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->property = Property::factory()->create();
});

test('guests cannot access reservations', function () {
    auth()->logout();

    $this->get(route('reservations.index'))->assertRedirect(route('login'));
});

test('index displays reservations', function () {
    Reservation::factory()->for($this->property)->create();

    $this->get(route('reservations.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('reservations/Index')
            ->has('reservations', 1)
            ->has('properties')
        );
});

test('create page renders', function () {
    $this->get(route('reservations.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('reservations/Create')
            ->has('properties')
            ->has('statuses')
        );
});

test('store creates a reservation', function () {
    $this->post(route('reservations.store'), [
        'property_id' => $this->property->id,
        'guest_name' => 'Juan Pérez',
        'guest_phone' => '+56912345678',
        'guest_email' => 'juan@test.com',
        'number_of_guests' => 3,
        'check_in' => now()->addDays(5)->format('Y-m-d'),
        'check_out' => now()->addDays(8)->format('Y-m-d'),
        'status' => ReservationStatus::Confirmed->value,
    ])->assertRedirect();

    $this->assertDatabaseHas('reservations', [
        'guest_name' => 'Juan Pérez',
        'property_id' => $this->property->id,
    ]);
});

test('store validates required fields', function () {
    $this->post(route('reservations.store'), [])
        ->assertSessionHasErrors(['property_id', 'guest_name', 'number_of_guests', 'check_in', 'check_out', 'status']);
});

test('store validates check_out is after check_in', function () {
    $this->post(route('reservations.store'), [
        'property_id' => $this->property->id,
        'guest_name' => 'Test',
        'number_of_guests' => 1,
        'check_in' => now()->addDays(5)->format('Y-m-d'),
        'check_out' => now()->addDays(3)->format('Y-m-d'),
        'status' => ReservationStatus::Confirmed->value,
    ])->assertSessionHasErrors('check_out');
});

test('store validates overlapping dates', function () {
    Reservation::factory()->for($this->property)->create([
        'check_in' => now()->addDays(5)->format('Y-m-d'),
        'check_out' => now()->addDays(8)->format('Y-m-d'),
    ]);

    $this->post(route('reservations.store'), [
        'property_id' => $this->property->id,
        'guest_name' => 'Overlap Test',
        'number_of_guests' => 1,
        'check_in' => now()->addDays(6)->format('Y-m-d'),
        'check_out' => now()->addDays(9)->format('Y-m-d'),
        'status' => ReservationStatus::Confirmed->value,
    ])->assertSessionHasErrors('check_in');
});

test('show displays a reservation', function () {
    $reservation = Reservation::factory()->for($this->property)->create();

    $this->get(route('reservations.show', $reservation))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('reservations/Show')
            ->has('reservation')
            ->has('isSameDayTurnover')
        );
});

test('edit page renders', function () {
    $reservation = Reservation::factory()->for($this->property)->create();

    $this->get(route('reservations.edit', $reservation))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('reservations/Edit')
            ->has('reservation')
            ->has('properties')
            ->has('statuses')
        );
});

test('update modifies a reservation', function () {
    $reservation = Reservation::factory()->for($this->property)->create();

    $this->put(route('reservations.update', $reservation), [
        'property_id' => $this->property->id,
        'guest_name' => 'Updated Guest',
        'number_of_guests' => 5,
        'check_in' => $reservation->check_in->format('Y-m-d'),
        'check_out' => $reservation->check_out->format('Y-m-d'),
        'status' => ReservationStatus::CheckedIn->value,
    ])->assertRedirect();

    $reservation->refresh();
    expect($reservation->guest_name)->toBe('Updated Guest')
        ->and($reservation->number_of_guests)->toBe(5)
        ->and($reservation->status)->toBe(ReservationStatus::CheckedIn);
});

test('destroy deletes a reservation', function () {
    $reservation = Reservation::factory()->for($this->property)->create();

    $this->delete(route('reservations.destroy', $reservation))
        ->assertRedirect(route('reservations.index'));

    $this->assertSoftDeleted('reservations', ['id' => $reservation->id]);
});
