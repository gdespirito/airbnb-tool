<?php

use App\Models\Property;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('guests cannot access properties', function () {
    auth()->logout();

    $this->get(route('properties.index'))->assertRedirect(route('login'));
});

test('index displays properties', function () {
    $property = Property::factory()->create();

    $this->get(route('properties.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('properties/Index')
            ->has('properties', 1)
        );
});

test('create page renders', function () {
    $this->get(route('properties.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('properties/Create'));
});

test('store creates a property', function () {
    $this->post(route('properties.store'), [
        'name' => 'Casa Test',
        'location' => 'Test City, Chile',
        'checkin_time' => '15:00',
        'checkout_time' => '12:00',
    ])->assertRedirect();

    $this->assertDatabaseHas('properties', [
        'name' => 'Casa Test',
        'slug' => 'casa-test',
    ]);
});

test('store validates required fields', function () {
    $this->post(route('properties.store'), [])
        ->assertSessionHasErrors(['name', 'location', 'checkin_time', 'checkout_time']);
});

test('show displays a property', function () {
    $property = Property::factory()->create();

    $this->get(route('properties.show', $property))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('properties/Show')
            ->has('property')
        );
});

test('edit page renders', function () {
    $property = Property::factory()->create();

    $this->get(route('properties.edit', $property))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('properties/Edit')
            ->has('property')
        );
});

test('update modifies a property', function () {
    $property = Property::factory()->create();

    $this->put(route('properties.update', $property), [
        'name' => 'Updated Name',
        'location' => $property->location,
        'checkin_time' => '16:00',
        'checkout_time' => '11:00',
    ])->assertRedirect();

    $property->refresh();
    expect($property->name)->toBe('Updated Name')
        ->and($property->checkin_time)->toBe('16:00');
});
