<?php

use App\Enums\CleaningTaskStatus;
use App\Models\CleaningTask;
use App\Models\Contact;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(User::factory()->create(), ['*']);
    $this->property = Property::factory()->create();
});

test('index returns all cleaning tasks', function () {
    CleaningTask::factory()->count(3)->for($this->property)->create();

    $this->getJson('/api/v1/cleaning-tasks')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('index filters by property_id', function () {
    $other = Property::factory()->create();
    CleaningTask::factory()->count(2)->for($this->property)->create();
    CleaningTask::factory()->for($other)->create();

    $response = $this->getJson("/api/v1/cleaning-tasks?property_id={$this->property->id}")
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(2);
});

test('index filters by status', function () {
    CleaningTask::factory()->for($this->property)->create(['status' => CleaningTaskStatus::Pending]);
    CleaningTask::factory()->for($this->property)->create(['status' => CleaningTaskStatus::Completed]);

    $response = $this->getJson('/api/v1/cleaning-tasks?status=pending')->assertSuccessful();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.status'))->toBe('pending');
});

test('index filters upcoming cleaning tasks', function () {
    CleaningTask::factory()->for($this->property)->create([
        'scheduled_date' => now()->addDays(3),
    ]);
    CleaningTask::factory()->for($this->property)->create([
        'scheduled_date' => now()->subDays(3),
    ]);

    $response = $this->getJson('/api/v1/cleaning-tasks?upcoming=1')->assertSuccessful();

    expect($response->json('data'))->toHaveCount(1);
});

test('show returns cleaning task with all relations', function () {
    $contact = Contact::factory()->create();
    $reservation = Reservation::factory()->for($this->property)->create();
    $task = CleaningTask::factory()->for($this->property)->for($reservation)->for($contact)->create();

    $response = $this->getJson("/api/v1/cleaning-tasks/{$task->id}")->assertSuccessful();

    expect($response->json('data.id'))->toBe($task->id)
        ->and($response->json('data.property.id'))->toBe($this->property->id)
        ->and($response->json('data.reservation.id'))->toBe($reservation->id)
        ->and($response->json('data.contact.id'))->toBe($contact->id);
});

test('updateStatus updates the cleaning task status', function () {
    $task = CleaningTask::factory()->for($this->property)->create([
        'status' => CleaningTaskStatus::Pending,
    ]);

    $response = $this->patchJson("/api/v1/cleaning-tasks/{$task->id}/status", [
        'status' => CleaningTaskStatus::Completed->value,
    ])->assertSuccessful();

    expect($response->json('data.status'))->toBe('completed');
    $this->assertDatabaseHas('cleaning_tasks', [
        'id' => $task->id,
        'status' => 'completed',
    ]);
});

test('updateStatus rejects invalid status', function () {
    $task = CleaningTask::factory()->for($this->property)->create();

    $this->patchJson("/api/v1/cleaning-tasks/{$task->id}/status", [
        'status' => 'invalid-status',
    ])->assertUnprocessable();
});

test('updateStatus requires status field', function () {
    $task = CleaningTask::factory()->for($this->property)->create();

    $this->patchJson("/api/v1/cleaning-tasks/{$task->id}/status", [])
        ->assertUnprocessable();
});
