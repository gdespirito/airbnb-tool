<?php

namespace Database\Factories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReservationNote>
 */
class ReservationNoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reservation_id' => Reservation::factory(),
            'content' => fake()->sentence(),
        ];
    }

    public function needsResponse(string $agent = 'alma'): static
    {
        return $this->state(fn (array $attributes) => [
            'from_agent' => $agent,
            'needs_response' => true,
        ]);
    }
}
