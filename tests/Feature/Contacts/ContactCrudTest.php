<?php

use App\Enums\ContactRole;
use App\Models\CleaningTask;
use App\Models\Contact;
use App\Models\Property;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('guests cannot access contacts', function () {
    auth()->logout();

    $this->get(route('contacts.index'))->assertRedirect(route('login'));
});

test('index displays contacts', function () {
    Contact::factory()->create();

    $this->get(route('contacts.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('contacts/Index')
            ->has('contacts', 1)
        );
});

test('index orders contacts by name', function () {
    Contact::factory()->create(['name' => 'Zara']);
    Contact::factory()->create(['name' => 'Ana']);

    $this->get(route('contacts.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('contacts/Index')
            ->has('contacts', 2)
            ->where('contacts.0.name', 'Ana')
            ->where('contacts.1.name', 'Zara')
        );
});

test('create page renders', function () {
    $this->get(route('contacts.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('contacts/Create')
            ->has('roles')
        );
});

test('store creates a contact', function () {
    $this->post(route('contacts.store'), [
        'name' => 'Eliene',
        'phone' => '+56999834369',
        'email' => 'eliene@example.com',
        'role' => ContactRole::Cleaning->value,
        'notes' => 'Great cleaner',
    ])->assertRedirect();

    $this->assertDatabaseHas('contacts', [
        'name' => 'Eliene',
        'phone' => '+56999834369',
        'role' => 'cleaning',
    ]);
});

test('store validates required fields', function () {
    $this->post(route('contacts.store'), [])
        ->assertSessionHasErrors(['name', 'role']);
});

test('store validates email format', function () {
    $this->post(route('contacts.store'), [
        'name' => 'Test',
        'email' => 'not-an-email',
        'role' => ContactRole::Cleaning->value,
    ])->assertSessionHasErrors('email');
});

test('show displays a contact with relationships', function () {
    $contact = Contact::factory()->create();
    $property = Property::factory()->create(['cleaning_contact_id' => $contact->id]);
    CleaningTask::factory()->for($property)->create(['contact_id' => $contact->id]);

    $this->get(route('contacts.show', $contact))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('contacts/Show')
            ->has('contact')
            ->has('contact.cleaning_tasks', 1)
            ->has('contact.properties', 1)
        );
});

test('edit page renders', function () {
    $contact = Contact::factory()->create();

    $this->get(route('contacts.edit', $contact))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('contacts/Edit')
            ->has('contact')
            ->has('roles')
        );
});

test('update modifies a contact', function () {
    $contact = Contact::factory()->create();

    $this->put(route('contacts.update', $contact), [
        'name' => 'Updated Name',
        'phone' => '+56912345678',
        'role' => ContactRole::Handyman->value,
    ])->assertRedirect();

    $contact->refresh();
    expect($contact->name)->toBe('Updated Name')
        ->and($contact->role)->toBe(ContactRole::Handyman);
});

test('destroy deletes a contact', function () {
    $contact = Contact::factory()->create();

    $this->delete(route('contacts.destroy', $contact))
        ->assertRedirect(route('contacts.index'));

    $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
});

test('destroy nullifies contact_id on cleaning tasks', function () {
    $contact = Contact::factory()->create();
    $property = Property::factory()->create();
    $task = CleaningTask::factory()->for($property)->create(['contact_id' => $contact->id]);

    $this->delete(route('contacts.destroy', $contact));

    $task->refresh();
    expect($task->contact_id)->toBeNull();
});

test('destroy nullifies cleaning_contact_id on properties', function () {
    $contact = Contact::factory()->create();
    $property = Property::factory()->create(['cleaning_contact_id' => $contact->id]);

    $this->delete(route('contacts.destroy', $contact));

    $property->refresh();
    expect($property->cleaning_contact_id)->toBeNull();
});
