<?php

namespace Database\Factories;

use App\Enums\ContactRole;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Contact> */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => '+569'.fake()->numerify('########'),
            'email' => fake()->optional()->safeEmail(),
            'role' => ContactRole::Cleaning,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function handyman(): static
    {
        return $this->state(fn () => ['role' => ContactRole::Handyman]);
    }

    public function other(): static
    {
        return $this->state(fn () => ['role' => ContactRole::Other]);
    }
}
