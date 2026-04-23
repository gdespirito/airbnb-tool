<?php

use App\Enums\CleaningTaskStatus;
use App\Enums\ReservationStatus;
use App\Mail\CleaningTaskCompleted;
use App\Models\CleaningTask;
use App\Models\Contact;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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

test('today returns active tasks scheduled up to today, not future ones', function () {
    CleaningTask::factory()->for($this->property)->create([
        'scheduled_date' => today(),
        'status' => CleaningTaskStatus::Pending,
    ]);
    CleaningTask::factory()->for($this->property)->create([
        'scheduled_date' => today()->addDays(1),
        'status' => CleaningTaskStatus::Pending,
    ]);

    $response = $this->getJson('/api/v1/cleaning-tasks/today')->assertSuccessful();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('meta.date'))->toBe(today()->toDateString());
});

test('today includes pending tasks from previous days (stickiness)', function () {
    CleaningTask::factory()->for($this->property)->create([
        'scheduled_date' => today()->subDays(3),
        'status' => CleaningTaskStatus::Pending,
    ]);
    CleaningTask::factory()->for($this->property)->create([
        'scheduled_date' => today(),
        'status' => CleaningTaskStatus::Notified,
    ]);

    $response = $this->getJson('/api/v1/cleaning-tasks/today')->assertSuccessful();

    expect($response->json('data'))->toHaveCount(2);
});

test('today excludes completed and skipped tasks', function () {
    CleaningTask::factory()->for($this->property)->create([
        'scheduled_date' => today(),
        'status' => CleaningTaskStatus::Completed,
    ]);
    CleaningTask::factory()->for($this->property)->create([
        'scheduled_date' => today()->subDays(2),
        'status' => CleaningTaskStatus::Skipped,
    ]);
    CleaningTask::factory()->for($this->property)->create([
        'scheduled_date' => today(),
        'status' => CleaningTaskStatus::Pending,
    ]);

    $response = $this->getJson('/api/v1/cleaning-tasks/today')->assertSuccessful();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.status'))->toBe('pending');
});

test('today includes same-day checkin info', function () {
    $task = CleaningTask::factory()->for($this->property)->create([
        'scheduled_date' => today(),
    ]);

    Reservation::factory()->for($this->property)->create([
        'check_in' => today(),
        'guest_name' => 'María González',
        'status' => ReservationStatus::Confirmed,
    ]);

    $response = $this->getJson('/api/v1/cleaning-tasks/today')->assertSuccessful();

    expect($response->json('data.0.has_same_day_checkin'))->toBeTrue()
        ->and($response->json('data.0.next_guest_name'))->toBe('María González');
});

test('today shows no same-day checkin when none exists', function () {
    CleaningTask::factory()->for($this->property)->create([
        'scheduled_date' => today(),
    ]);

    $response = $this->getJson('/api/v1/cleaning-tasks/today')->assertSuccessful();

    expect($response->json('data.0.has_same_day_checkin'))->toBeFalse();
});

test('today filters by property_id', function () {
    $other = Property::factory()->create();
    CleaningTask::factory()->for($this->property)->create(['scheduled_date' => today()]);
    CleaningTask::factory()->for($other)->create(['scheduled_date' => today()]);

    $response = $this->getJson("/api/v1/cleaning-tasks/today?property_id={$this->property->id}")
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(1);
});

test('update sets estimated_arrival_time', function () {
    $task = CleaningTask::factory()->for($this->property)->create();

    $response = $this->patchJson("/api/v1/cleaning-tasks/{$task->id}", [
        'estimated_arrival_time' => '14:00',
    ])->assertSuccessful();

    expect($response->json('data.estimated_arrival_time'))->toBe('14:00');
    $this->assertDatabaseHas('cleaning_tasks', [
        'id' => $task->id,
        'estimated_arrival_time' => '14:00',
    ]);
});

test('update sets started_at when status changes to in_progress', function () {
    $task = CleaningTask::factory()->for($this->property)->create([
        'status' => CleaningTaskStatus::Pending,
    ]);

    $response = $this->patchJson("/api/v1/cleaning-tasks/{$task->id}", [
        'status' => CleaningTaskStatus::InProgress->value,
    ])->assertSuccessful();

    expect($response->json('data.status'))->toBe('in_progress')
        ->and($response->json('data.started_at'))->not->toBeNull();

    $task->refresh();
    expect($task->started_at)->not->toBeNull();
});

test('update does not overwrite existing started_at', function () {
    $existingStartedAt = now()->subHour();
    $task = CleaningTask::factory()->for($this->property)->create([
        'status' => CleaningTaskStatus::InProgress,
        'started_at' => $existingStartedAt,
    ]);

    $this->patchJson("/api/v1/cleaning-tasks/{$task->id}", [
        'status' => CleaningTaskStatus::InProgress->value,
        'notes' => 'Updated notes',
    ])->assertSuccessful();

    $task->refresh();
    expect($task->started_at->timestamp)->toBe($existingStartedAt->timestamp);
});

test('complete sets completed_at and status and sends email', function () {
    Mail::fake();

    $task = CleaningTask::factory()->for($this->property)->create([
        'status' => CleaningTaskStatus::InProgress,
    ]);

    $response = $this->postJson("/api/v1/cleaning-tasks/{$task->id}/complete", [
        'notes' => 'All clean',
    ])->assertSuccessful();

    expect($response->json('data.status'))->toBe('completed')
        ->and($response->json('data.completed_at'))->not->toBeNull()
        ->and($response->json('data.notes'))->toBe('All clean');

    Mail::assertSent(CleaningTaskCompleted::class);
});

test('storePhotos uploads and stores photos', function () {
    Storage::fake('s3');

    $task = CleaningTask::factory()->for($this->property)->create();

    $response = $this->postJson("/api/v1/cleaning-tasks/{$task->id}/photos", [
        'photos' => [
            UploadedFile::fake()->image('photo1.jpg', 800, 600),
            UploadedFile::fake()->image('photo2.png', 800, 600),
        ],
    ])->assertSuccessful();

    expect($response->json('data.photos'))->toHaveCount(2);

    $this->assertDatabaseCount('cleaning_task_photos', 2);
    $this->assertDatabaseHas('cleaning_task_photos', [
        'cleaning_task_id' => $task->id,
        'disk' => 's3',
        'original_filename' => 'photo1.jpg',
    ]);
});

test('storePhotos rejects invalid file types', function () {
    Storage::fake('s3');

    $task = CleaningTask::factory()->for($this->property)->create();

    $this->postJson("/api/v1/cleaning-tasks/{$task->id}/photos", [
        'photos' => [
            UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ],
    ])->assertUnprocessable();
});

test('storePhotos rejects more than 5 photos', function () {
    Storage::fake('s3');

    $task = CleaningTask::factory()->for($this->property)->create();

    $photos = [];
    for ($i = 1; $i <= 6; $i++) {
        $photos[] = UploadedFile::fake()->image("photo{$i}.jpg", 800, 600);
    }

    $this->postJson("/api/v1/cleaning-tasks/{$task->id}/photos", [
        'photos' => $photos,
    ])->assertUnprocessable();
});

test('store creates a cleaning task manually', function () {
    $contact = Contact::factory()->create();

    $response = $this->postJson('/api/v1/cleaning-tasks', [
        'property_id' => $this->property->id,
        'contact_id' => $contact->id,
        'scheduled_date' => today()->toDateString(),
        'cleaning_type' => 'checkout',
        'notes' => 'Creada manualmente por red de seguridad',
    ])->assertSuccessful();

    expect($response->json('data.property.id'))->toBe($this->property->id)
        ->and($response->json('data.status'))->toBe('pending');

    $this->assertDatabaseHas('cleaning_tasks', [
        'property_id' => $this->property->id,
        'contact_id' => $contact->id,
        'notes' => 'Creada manualmente por red de seguridad',
        'status' => 'pending',
    ]);
});

test('store requires property_id and scheduled_date', function () {
    $this->postJson('/api/v1/cleaning-tasks', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['property_id', 'scheduled_date']);
});

test('checking in a reservation skips stale cleaning tasks in same property', function () {
    $reservation = Reservation::factory()->for($this->property)->create([
        'check_in' => today(),
        'check_out' => today()->addDays(3),
        'status' => ReservationStatus::Confirmed,
    ]);

    $staleTask = CleaningTask::factory()->for($this->property)->create([
        'scheduled_date' => today()->subDays(4),
        'status' => CleaningTaskStatus::Pending,
    ]);

    $reservation->update(['status' => ReservationStatus::CheckedIn]);

    expect($staleTask->fresh()->status)->toBe(CleaningTaskStatus::Skipped);
});

test('checking in a reservation does not skip tasks from other properties', function () {
    $other = Property::factory()->create();

    $reservation = Reservation::factory()->for($this->property)->create([
        'check_in' => today(),
        'check_out' => today()->addDays(3),
        'status' => ReservationStatus::Confirmed,
    ]);

    $otherTask = CleaningTask::factory()->for($other)->create([
        'scheduled_date' => today()->subDays(2),
        'status' => CleaningTaskStatus::Pending,
    ]);

    $reservation->update(['status' => ReservationStatus::CheckedIn]);

    expect($otherTask->fresh()->status)->toBe(CleaningTaskStatus::Pending);
});

test('checking in a reservation does not skip future cleaning tasks', function () {
    $reservation = Reservation::factory()->for($this->property)->create([
        'check_in' => today(),
        'check_out' => today()->addDays(3),
        'status' => ReservationStatus::Confirmed,
    ]);

    $futureTask = CleaningTask::factory()->for($this->property)->create([
        'scheduled_date' => today()->addDays(2),
        'status' => CleaningTaskStatus::Pending,
    ]);

    $reservation->update(['status' => ReservationStatus::CheckedIn]);

    expect($futureTask->fresh()->status)->toBe(CleaningTaskStatus::Pending);
});

test('checking in a reservation does not affect completed tasks', function () {
    $reservation = Reservation::factory()->for($this->property)->create([
        'check_in' => today(),
        'check_out' => today()->addDays(3),
        'status' => ReservationStatus::Confirmed,
    ]);

    $completedTask = CleaningTask::factory()->for($this->property)->create([
        'scheduled_date' => today()->subDays(2),
        'status' => CleaningTaskStatus::Completed,
    ]);

    $reservation->update(['status' => ReservationStatus::CheckedIn]);

    expect($completedTask->fresh()->status)->toBe(CleaningTaskStatus::Completed);
});

test('today reports next guest on sticky task when check-in is today', function () {
    CleaningTask::factory()->for($this->property)->create([
        'scheduled_date' => today()->subDays(4),
        'status' => CleaningTaskStatus::Pending,
    ]);

    Reservation::factory()->for($this->property)->create([
        'check_in' => today(),
        'guest_name' => 'Morgan Richter',
        'status' => ReservationStatus::Confirmed,
    ]);

    $response = $this->getJson('/api/v1/cleaning-tasks/today')->assertSuccessful();

    expect($response->json('data.0.has_same_day_checkin'))->toBeTrue()
        ->and($response->json('data.0.next_guest_name'))->toBe('Morgan Richter')
        ->and($response->json('data.0.is_sticky'))->toBeTrue()
        ->and($response->json('data.0.days_overdue'))->toBe(4);
});

test('today does not flag today-scheduled tasks as sticky', function () {
    CleaningTask::factory()->for($this->property)->create([
        'scheduled_date' => today(),
        'status' => CleaningTaskStatus::Pending,
    ]);

    $response = $this->getJson('/api/v1/cleaning-tasks/today')->assertSuccessful();

    expect($response->json('data.0.is_sticky'))->toBeFalse()
        ->and($response->json('data.0.days_overdue'))->toBe(0);
});

test('store requires authentication', function () {
    app('auth')->forgetGuards();

    $this->postJson('/api/v1/cleaning-tasks', [
        'property_id' => $this->property->id,
        'scheduled_date' => today()->toDateString(),
    ])->assertUnauthorized();
});

test('store rejects reservation_id that belongs to a different property', function () {
    $other = Property::factory()->create();
    $reservation = Reservation::factory()->for($other)->create();

    $this->postJson('/api/v1/cleaning-tasks', [
        'property_id' => $this->property->id,
        'reservation_id' => $reservation->id,
        'scheduled_date' => today()->toDateString(),
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['reservation_id']);
});

test('store defaults cleaning_type to checkout when omitted', function () {
    $response = $this->postJson('/api/v1/cleaning-tasks', [
        'property_id' => $this->property->id,
        'scheduled_date' => today()->toDateString(),
    ])->assertSuccessful();

    expect($response->json('data.cleaning_type'))->toBe('checkout');
});

test('updating check_in and status together uses the new check_in for skipping', function () {
    $reservation = Reservation::factory()->for($this->property)->create([
        'check_in' => today(),
        'check_out' => today()->addDays(3),
        'status' => ReservationStatus::Confirmed,
    ]);

    $taskBeforeOriginalCheckIn = CleaningTask::factory()->for($this->property)->create([
        'scheduled_date' => today()->subDays(1),
        'status' => CleaningTaskStatus::Pending,
    ]);

    // La tarea está antes del check_in original pero después del check_in nuevo.
    // Si el observer usara el valor VIEJO, la marcaría como skipped.
    // Debe usar el valor NUEVO, y por lo tanto NO marcarla.
    $reservation->update([
        'check_in' => today()->subDays(2),
        'status' => ReservationStatus::CheckedIn,
    ]);

    expect($taskBeforeOriginalCheckIn->fresh()->status)->toBe(CleaningTaskStatus::Pending);
});
