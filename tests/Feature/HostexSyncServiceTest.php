<?php

use App\Enums\ReservationStatus;
use App\Models\Property;
use App\Models\Reservation;
use App\Services\HostexSyncService;

beforeEach(function () {
    $this->service = new HostexSyncService;
    $this->property = Property::factory()->create([
        'hostex_property_id' => 'hostex-prop-1',
    ]);
});

function hostexData(array $overrides = []): array
{
    return array_merge([
        'reservation_code' => 'HMTX-12345',
        'property_id' => 'hostex-prop-1',
        'guest_name' => 'María García',
        'guest_phone' => '+56912345678',
        'guest_email' => 'maria@example.com',
        'number_of_guests' => 4,
        'number_of_adults' => 2,
        'number_of_children' => 1,
        'number_of_infants' => 1,
        'number_of_pets' => 0,
        'check_in_date' => '2026-03-10',
        'check_out_date' => '2026-03-15',
        'status' => 'accepted',
        'channel_type' => 'airbnb',
        'booked_at' => '2026-02-18T10:30:00Z',
        'conversation_id' => 'conv-abc123',
        'check_in_details' => [
            'arrival_at' => '15:00',
            'departure_at' => '11:00',
            'lock_code' => '1234',
        ],
        'rates' => [
            'total_rate' => [
                'amount' => '150000.00',
                'currency' => 'CLP',
            ],
        ],
    ], $overrides);
}

test('creates a new reservation from hostex data', function () {
    $reservation = $this->service->upsertFromHostexData(hostexData());

    expect($reservation)->not->toBeNull()
        ->and($reservation->airbnb_reservation_id)->toBe('HMTX-12345')
        ->and($reservation->property_id)->toBe($this->property->id)
        ->and($reservation->guest_name)->toBe('María García')
        ->and($reservation->guest_phone)->toBe('+56912345678')
        ->and($reservation->guest_email)->toBe('maria@example.com')
        ->and($reservation->number_of_guests)->toBe(4)
        ->and($reservation->number_of_adults)->toBe(2)
        ->and($reservation->number_of_children)->toBe(1)
        ->and($reservation->number_of_infants)->toBe(1)
        ->and($reservation->number_of_pets)->toBe(0)
        ->and($reservation->check_in->toDateString())->toBe('2026-03-10')
        ->and($reservation->check_out->toDateString())->toBe('2026-03-15')
        ->and($reservation->status)->toBe(ReservationStatus::Confirmed)
        ->and($reservation->source)->toBe('hostex')
        ->and($reservation->channel_type)->toBe('airbnb')
        ->and($reservation->total_price)->toBe('150000.00')
        ->and($reservation->currency)->toBe('CLP')
        ->and($reservation->check_in_time)->toBe('15:00')
        ->and($reservation->check_out_time)->toBe('11:00')
        ->and($reservation->lock_code)->toBe('1234')
        ->and($reservation->hostex_conversation_id)->toBe('conv-abc123');
});

test('updates an existing reservation', function () {
    $this->service->upsertFromHostexData(hostexData());

    $reservation = $this->service->upsertFromHostexData(hostexData([
        'guest_name' => 'María García López',
        'number_of_guests' => 5,
    ]));

    expect($reservation->guest_name)->toBe('María García López')
        ->and($reservation->number_of_guests)->toBe(5)
        ->and(Reservation::where('airbnb_reservation_id', 'HMTX-12345')->count())->toBe(1);
});

test('maps hostex statuses correctly', function (string $hostexStatus, ReservationStatus $expected) {
    $reservation = $this->service->upsertFromHostexData(hostexData([
        'reservation_code' => "RES-{$hostexStatus}",
        'status' => $hostexStatus,
    ]));

    expect($reservation->status)->toBe($expected);
})->with([
    'accepted' => ['accepted', ReservationStatus::Confirmed],
    'cancelled' => ['cancelled', ReservationStatus::Cancelled],
    'denied' => ['denied', ReservationStatus::Cancelled],
    'timeout' => ['timeout', ReservationStatus::Cancelled],
    'wait_accept' => ['wait_accept', ReservationStatus::Confirmed],
    'wait_pay' => ['wait_pay', ReservationStatus::Confirmed],
]);

test('returns null for unknown property', function () {
    $result = $this->service->upsertFromHostexData(hostexData([
        'property_id' => 'unknown-property',
    ]));

    expect($result)->toBeNull();
});

test('returns null when missing required fields', function () {
    $result = $this->service->upsertFromHostexData(['guest_name' => 'Test']);

    expect($result)->toBeNull();
});

test('restores soft-deleted reservation', function () {
    $reservation = Reservation::factory()->for($this->property)->create([
        'airbnb_reservation_id' => 'HMTX-DELETED',
    ]);
    $reservation->delete();

    expect(Reservation::where('airbnb_reservation_id', 'HMTX-DELETED')->exists())->toBeFalse();

    $restored = $this->service->upsertFromHostexData(hostexData([
        'reservation_code' => 'HMTX-DELETED',
    ]));

    expect($restored)->not->toBeNull()
        ->and($restored->trashed())->toBeFalse()
        ->and($restored->guest_name)->toBe('María García');
});

test('observer creates cleaning task for synced reservation', function () {
    // Use a new reservation code so it triggers 'created' event
    $reservation = $this->service->upsertFromHostexData(hostexData([
        'reservation_code' => 'HMTX-NEW',
    ]));

    expect($reservation->cleaningTask)->not->toBeNull()
        ->and($reservation->cleaningTask->scheduled_date->toDateString())->toBe('2026-03-15')
        ->and($reservation->cleaningTask->property_id)->toBe($this->property->id);
});

test('stores extra data in metadata', function () {
    $reservation = $this->service->upsertFromHostexData(hostexData([
        'tags' => ['vip', 'returning'],
        'custom_fields' => ['note' => 'Needs extra towels'],
    ]));

    expect($reservation->metadata)->toHaveKey('hostex_tags')
        ->and($reservation->metadata['hostex_tags'])->toBe(['vip', 'returning'])
        ->and($reservation->metadata['hostex_custom_fields'])->toBe(['note' => 'Needs extra towels']);
});
