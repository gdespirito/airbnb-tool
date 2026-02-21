<?php

use App\Mail\ReservationNoteCreated;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\ReservationNote;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(User::factory()->create(), ['*']);
    $this->property = Property::factory()->create();
    $this->reservation = Reservation::factory()->for($this->property)->create();
});

test('index returns notes for a reservation', function () {
    ReservationNote::factory()->count(3)->for($this->reservation)->create();

    $other = Reservation::factory()->for($this->property)->create();
    ReservationNote::factory()->count(2)->for($other)->create();

    $response = $this->getJson("/api/v1/reservations/{$this->reservation->id}/notes")
        ->assertSuccessful();

    expect($response->json('data'))->toHaveCount(3);
});

test('store creates a note and validates content required', function () {
    $this->postJson("/api/v1/reservations/{$this->reservation->id}/notes", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['content']);

    $response = $this->postJson("/api/v1/reservations/{$this->reservation->id}/notes", [
        'content' => 'Guest asked for late check-out',
    ])->assertCreated();

    expect($response->json('data.content'))->toBe('Guest asked for late check-out')
        ->and($response->json('data.reservation_id'))->toBe($this->reservation->id);

    $this->assertDatabaseHas('reservation_notes', [
        'reservation_id' => $this->reservation->id,
        'content' => 'Guest asked for late check-out',
    ]);
});

test('store sends email to all users', function () {
    Mail::fake();

    $anotherUser = User::factory()->create();

    $this->postJson("/api/v1/reservations/{$this->reservation->id}/notes", [
        'content' => 'Important note',
    ])->assertCreated();

    Mail::assertSent(ReservationNoteCreated::class, function (ReservationNoteCreated $mail) {
        return $mail->note->content === 'Important note';
    });
});

test('show returns a single note', function () {
    $note = ReservationNote::factory()->for($this->reservation)->create();

    $response = $this->getJson("/api/v1/reservation-notes/{$note->id}")
        ->assertSuccessful();

    expect($response->json('data.id'))->toBe($note->id)
        ->and($response->json('data.content'))->toBe($note->content);
});

test('update modifies note content', function () {
    $note = ReservationNote::factory()->for($this->reservation)->create(['content' => 'Old content']);

    $response = $this->putJson("/api/v1/reservation-notes/{$note->id}", [
        'content' => 'Updated content',
    ])->assertSuccessful();

    expect($response->json('data.content'))->toBe('Updated content');
    $this->assertDatabaseHas('reservation_notes', ['id' => $note->id, 'content' => 'Updated content']);
});

test('destroy soft deletes a note', function () {
    $note = ReservationNote::factory()->for($this->reservation)->create();

    $this->deleteJson("/api/v1/reservation-notes/{$note->id}")->assertNoContent();

    $this->assertSoftDeleted('reservation_notes', ['id' => $note->id]);
});

test('reservation show includes notes', function () {
    ReservationNote::factory()->count(2)->for($this->reservation)->create();

    $response = $this->getJson("/api/v1/reservations/{$this->reservation->id}")
        ->assertSuccessful();

    expect($response->json('data.reservation_notes'))->toHaveCount(2);
});
