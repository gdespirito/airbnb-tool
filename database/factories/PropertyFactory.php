<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'airbnb_url' => null,
            'airbnb_listing_id' => null,
            'ical_url' => null,
            'location' => fake()->city().', Chile',
            'latitude' => fake()->latitude(-56, -17),
            'longitude' => fake()->longitude(-75, -66),
            'checkin_time' => '15:00',
            'checkout_time' => '12:00',
            'cleaning_contact_name' => fake()->name(),
            'cleaning_contact_phone' => '+569'.fake()->numerify('########'),
            'metadata' => null,
        ];
    }
}
