<?php

use App\Enums\CleaningTaskStatus;
use App\Enums\ReservationStatus;
use App\Models\Property;
use App\Models\Reservation;

beforeEach(function () {
    $this->property = Property::factory()->create();
});

test('command skips tasks of cancelled reservations', function () {
    $reservation = Reservation::factory()->for($this->property)->create([
        'status' => ReservationStatus::Confirmed,
    ]);
    // La cancelación dispara el observer y skipea la task automáticamente,
    // pero para el escenario histórico (pre-fix) forzamos la task a Pending
    // después del cambio de status.
    $task = $reservation->cleaningTask;

    $reservation->updateQuietly(['status' => ReservationStatus::Cancelled]);
    $task->update(['status' => CleaningTaskStatus::Pending]);

    $this->artisan('cleaning:skip-orphans')
        ->expectsOutputToContain('1')
        ->assertSuccessful();

    expect($task->fresh()->status)->toBe(CleaningTaskStatus::Skipped);
});

test('command skips tasks superseded by later arrivals', function () {
    $earlier = Reservation::factory()->for($this->property)->create([
        'check_in' => now()->subDays(10),
        'check_out' => now()->subDays(7),
        'status' => ReservationStatus::Confirmed,
    ]);
    $earlier->updateQuietly(['status' => ReservationStatus::CheckedOut]);
    $earlierTask = $earlier->cleaningTask;
    $earlierTask->update(['status' => CleaningTaskStatus::Pending, 'scheduled_date' => now()->subDays(7)]);

    Reservation::factory()->for($this->property)->create([
        'check_in' => now()->subDays(5),
        'check_out' => now()->subDays(2),
        'status' => ReservationStatus::Confirmed,
    ])->updateQuietly(['status' => ReservationStatus::CheckedOut]);

    $this->artisan('cleaning:skip-orphans')->assertSuccessful();

    expect($earlierTask->fresh()->status)->toBe(CleaningTaskStatus::Skipped);
});

test('command does not touch tasks of active confirmed reservations', function () {
    $reservation = Reservation::factory()->for($this->property)->create([
        'check_in' => now()->addDays(3),
        'check_out' => now()->addDays(6),
        'status' => ReservationStatus::Confirmed,
    ]);
    $task = $reservation->cleaningTask;

    $this->artisan('cleaning:skip-orphans')->assertSuccessful();

    expect($task->fresh()->status)->toBe(CleaningTaskStatus::Pending);
});

test('command does not touch tasks when checked-out reservation has no successor', function () {
    $reservation = Reservation::factory()->for($this->property)->create([
        'check_in' => now()->subDays(5),
        'check_out' => now()->subDays(2),
        'status' => ReservationStatus::Confirmed,
    ]);
    $reservation->updateQuietly(['status' => ReservationStatus::CheckedOut]);
    $task = $reservation->cleaningTask;
    $task->update(['status' => CleaningTaskStatus::Pending, 'scheduled_date' => now()->subDays(2)]);

    $this->artisan('cleaning:skip-orphans')->assertSuccessful();

    expect($task->fresh()->status)->toBe(CleaningTaskStatus::Pending);
});

test('dry-run does not modify data', function () {
    $reservation = Reservation::factory()->for($this->property)->create([
        'status' => ReservationStatus::Confirmed,
    ]);
    $task = $reservation->cleaningTask;
    $reservation->updateQuietly(['status' => ReservationStatus::Cancelled]);
    $task->update(['status' => CleaningTaskStatus::Pending]);

    $this->artisan('cleaning:skip-orphans', ['--dry-run' => true])
        ->expectsOutputToContain('Dry run')
        ->assertSuccessful();

    expect($task->fresh()->status)->toBe(CleaningTaskStatus::Pending);
});
