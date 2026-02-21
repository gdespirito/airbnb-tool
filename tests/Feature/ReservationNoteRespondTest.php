<?php

use App\Jobs\NotifyAgentResponse;
use App\Models\ReservationNote;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('guests cannot respond to reservation notes', function () {
    auth()->logout();

    $note = ReservationNote::factory()->needsResponse()->create();

    $this->put(route('reservation-notes.respond', $note), [
        'content' => 'My response',
    ])->assertRedirect(route('login'));
});

test('owner can respond to an agent note', function () {
    Queue::fake();

    $note = ReservationNote::factory()->needsResponse()->create();

    $this->put(route('reservation-notes.respond', $note), [
        'content' => 'Thanks, I will handle it.',
    ])->assertRedirect();

    $note->refresh();

    expect($note->content)->toBe('Thanks, I will handle it.')
        ->and($note->responded_at)->not->toBeNull();

    Queue::assertPushed(NotifyAgentResponse::class, function ($job) use ($note) {
        return $job->note->id === $note->id;
    });
});

test('responding requires content', function () {
    $note = ReservationNote::factory()->needsResponse()->create();

    $this->put(route('reservation-notes.respond', $note), [
        'content' => '',
    ])->assertSessionHasErrors('content');
});

test('content cannot exceed 2000 characters', function () {
    $note = ReservationNote::factory()->needsResponse()->create();

    $this->put(route('reservation-notes.respond', $note), [
        'content' => str_repeat('a', 2001),
    ])->assertSessionHasErrors('content');
});
