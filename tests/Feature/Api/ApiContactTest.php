<?php

use App\Models\Contact;
use App\Models\Property;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('unauthenticated requests are rejected', function () {
    $this->getJson('/api/v1/contacts')->assertUnauthorized();
});

describe('authenticated', function () {
    beforeEach(function () {
        Sanctum::actingAs(User::factory()->create(), ['*']);
    });

    test('index returns contacts ordered by name', function () {
        Contact::factory()->create(['name' => 'Viviana']);
        Contact::factory()->create(['name' => 'Eliene']);

        $response = $this->getJson('/api/v1/contacts')->assertSuccessful();

        expect($response->json('data.0.name'))->toBe('Eliene')
            ->and($response->json('data.1.name'))->toBe('Viviana');
    });

    test('index filters by property_id', function () {
        $contact = Contact::factory()->create(['name' => 'Ana']);
        $other = Contact::factory()->create(['name' => 'Luis']);
        $property = Property::factory()->create(['cleaning_contact_id' => $contact->id]);
        Property::factory()->create(['cleaning_contact_id' => $other->id]);

        $response = $this->getJson("/api/v1/contacts?property_id={$property->id}")->assertSuccessful();

        expect($response->json('data'))->toHaveCount(1)
            ->and($response->json('data.0.name'))->toBe('Ana');
    });

    test('index returns empty when property has no contacts', function () {
        $property = Property::factory()->create(['cleaning_contact_id' => null]);

        $this->getJson("/api/v1/contacts?property_id={$property->id}")
            ->assertSuccessful()
            ->assertJsonCount(0, 'data');
    });

    test('show returns a single contact', function () {
        $contact = Contact::factory()->create();

        $response = $this->getJson("/api/v1/contacts/{$contact->id}")->assertSuccessful();

        expect($response->json('data.id'))->toBe($contact->id)
            ->and($response->json('data.name'))->toBe($contact->name);
    });

    test('show returns 404 for unknown contact', function () {
        $this->getJson('/api/v1/contacts/999')->assertNotFound();
    });
});
