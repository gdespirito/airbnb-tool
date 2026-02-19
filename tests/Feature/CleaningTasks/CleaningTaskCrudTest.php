<?php

use App\Enums\CleaningTaskStatus;
use App\Enums\CleaningType;
use App\Enums\ReservationStatus;
use App\Models\CleaningTask;
use App\Models\Contact;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->property = Property::factory()->create();
});

test('guests cannot access cleaning tasks', function () {
    auth()->logout();

    $this->get(route('cleaning-tasks.index'))->assertRedirect(route('login'));
});

test('index displays cleaning tasks', function () {
    CleaningTask::factory()->for($this->property)->create();

    $this->get(route('cleaning-tasks.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('cleaning-tasks/Index')
            ->has('cleaningTasks', 1)
        );
});

test('create page renders', function () {
    $this->get(route('cleaning-tasks.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('cleaning-tasks/Create')
            ->has('properties')
            ->has('contacts')
            ->has('statuses')
            ->has('cleaningTypes')
        );
});

test('store creates a cleaning task', function () {
    $this->post(route('cleaning-tasks.store'), [
        'property_id' => $this->property->id,
        'status' => CleaningTaskStatus::Pending->value,
        'cleaning_type' => CleaningType::Checkout->value,
        'cleaning_fee' => 25000,
        'scheduled_date' => now()->addDays(5)->format('Y-m-d'),
        'assigned_to' => 'Eliene',
        'assigned_phone' => '+56999834369',
    ])->assertRedirect();

    $this->assertDatabaseHas('cleaning_tasks', [
        'property_id' => $this->property->id,
        'assigned_to' => 'Eliene',
        'cleaning_fee' => 25000,
    ]);
});

test('store validates required fields', function () {
    $this->post(route('cleaning-tasks.store'), [])
        ->assertSessionHasErrors(['property_id', 'status', 'cleaning_type', 'scheduled_date']);
});

test('store validates invalid property', function () {
    $this->post(route('cleaning-tasks.store'), [
        'property_id' => 999,
        'status' => CleaningTaskStatus::Pending->value,
        'cleaning_type' => CleaningType::Checkout->value,
        'scheduled_date' => now()->addDays(5)->format('Y-m-d'),
    ])->assertSessionHasErrors('property_id');
});

test('show displays a cleaning task', function () {
    $task = CleaningTask::factory()->for($this->property)->create();

    $this->get(route('cleaning-tasks.show', $task))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('cleaning-tasks/Show')
            ->has('cleaningTask')
        );
});

test('edit page renders', function () {
    $task = CleaningTask::factory()->for($this->property)->create();

    $this->get(route('cleaning-tasks.edit', $task))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('cleaning-tasks/Edit')
            ->has('cleaningTask')
            ->has('properties')
            ->has('statuses')
            ->has('cleaningTypes')
        );
});

test('update modifies a cleaning task', function () {
    $task = CleaningTask::factory()->for($this->property)->create();

    $this->put(route('cleaning-tasks.update', $task), [
        'property_id' => $this->property->id,
        'status' => CleaningTaskStatus::Completed->value,
        'cleaning_type' => CleaningType::DeepClean->value,
        'cleaning_fee' => 30000,
        'scheduled_date' => now()->addDays(3)->format('Y-m-d'),
        'assigned_to' => 'Viviana',
    ])->assertRedirect();

    $task->refresh();
    expect($task->status)->toBe(CleaningTaskStatus::Completed)
        ->and($task->cleaning_type)->toBe(CleaningType::DeepClean)
        ->and($task->cleaning_fee)->toBe(30000)
        ->and($task->assigned_to)->toBe('Viviana');
});

test('destroy deletes a cleaning task', function () {
    $task = CleaningTask::factory()->for($this->property)->create();

    $this->delete(route('cleaning-tasks.destroy', $task))
        ->assertRedirect(route('cleaning-tasks.index'));

    $this->assertDatabaseMissing('cleaning_tasks', ['id' => $task->id]);
});

test('store creates a cleaning task with contact_id', function () {
    $contact = Contact::factory()->create();

    $this->post(route('cleaning-tasks.store'), [
        'property_id' => $this->property->id,
        'contact_id' => $contact->id,
        'status' => CleaningTaskStatus::Pending->value,
        'cleaning_type' => CleaningType::Checkout->value,
        'scheduled_date' => now()->addDays(5)->format('Y-m-d'),
    ])->assertRedirect();

    $this->assertDatabaseHas('cleaning_tasks', [
        'property_id' => $this->property->id,
        'contact_id' => $contact->id,
    ]);
});

test('observer auto-creates cleaning task when confirmed reservation is created', function () {
    $this->property->update([
        'cleaning_contact_name' => 'Eliene',
        'cleaning_contact_phone' => '+56999834369',
        'metadata' => ['cleaning_fee' => 25000],
    ]);

    $reservation = Reservation::factory()->for($this->property)->create([
        'status' => ReservationStatus::Confirmed,
        'check_out' => now()->addDays(8)->format('Y-m-d'),
    ]);

    $task = $reservation->cleaningTask;
    expect($task)->not->toBeNull()
        ->and($task->property_id)->toBe($this->property->id)
        ->and($task->cleaning_type)->toBe(CleaningType::Checkout)
        ->and($task->scheduled_date->format('Y-m-d'))->toBe(now()->addDays(8)->format('Y-m-d'))
        ->and($task->cleaning_fee)->toBe(25000)
        ->and($task->assigned_to)->toBe('Eliene')
        ->and($task->assigned_phone)->toBe('+56999834369');
});

test('observer sets contact_id from property cleaning contact', function () {
    $contact = Contact::factory()->create();
    $this->property->update([
        'cleaning_contact_id' => $contact->id,
        'metadata' => ['cleaning_fee' => 30000],
    ]);

    $reservation = Reservation::factory()->for($this->property)->create([
        'status' => ReservationStatus::Confirmed,
        'check_out' => now()->addDays(5)->format('Y-m-d'),
    ]);

    $task = $reservation->cleaningTask;
    expect($task)->not->toBeNull()
        ->and($task->contact_id)->toBe($contact->id);
});

test('observer does not create cleaning task for cancelled reservations', function () {
    $reservation = Reservation::factory()->for($this->property)->cancelled()->create();

    expect($reservation->cleaningTask)->toBeNull();
    $this->assertDatabaseCount('cleaning_tasks', 0);
});

test('observer updates scheduled date when checkout date changes', function () {
    $reservation = Reservation::factory()->for($this->property)->create([
        'status' => ReservationStatus::Confirmed,
        'check_in' => now()->addDays(5)->format('Y-m-d'),
        'check_out' => now()->addDays(8)->format('Y-m-d'),
    ]);

    $newCheckout = now()->addDays(10)->format('Y-m-d');
    $reservation->update(['check_out' => $newCheckout]);

    $reservation->refresh();
    expect($reservation->cleaningTask->scheduled_date->format('Y-m-d'))->toBe($newCheckout);
});
