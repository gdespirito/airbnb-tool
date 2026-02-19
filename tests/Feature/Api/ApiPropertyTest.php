<?php

use App\Models\Contact;
use App\Models\Property;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('unauthenticated requests are rejected', function () {
    $this->getJson('/api/v1/properties')->assertUnauthorized();
});

describe('authenticated', function () {
    beforeEach(function () {
        Sanctum::actingAs(User::factory()->create(), ['*']);
    });

    test('index returns all properties', function () {
        Property::factory()->count(2)->create();

        $this->getJson('/api/v1/properties')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data');
    });

    test('index includes cleaning contact when loaded', function () {
        $contact = Contact::factory()->create();
        Property::factory()->create(['cleaning_contact_id' => $contact->id]);

        $response = $this->getJson('/api/v1/properties')->assertSuccessful();

        expect($response->json('data.0.cleaning_contact.id'))->toBe($contact->id);
    });

    test('show returns a single property with cleaning contact', function () {
        $contact = Contact::factory()->create();
        $property = Property::factory()->create(['cleaning_contact_id' => $contact->id]);

        $response = $this->getJson("/api/v1/properties/{$property->id}")->assertSuccessful();

        expect($response->json('data.id'))->toBe($property->id)
            ->and($response->json('data.cleaning_contact.id'))->toBe($contact->id);
    });

    test('show returns 404 for unknown property', function () {
        $this->getJson('/api/v1/properties/999')->assertNotFound();
    });
});
