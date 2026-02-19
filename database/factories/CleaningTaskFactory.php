<?php

namespace Database\Factories;

use App\Enums\CleaningTaskStatus;
use App\Enums\CleaningType;
use App\Models\Property;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CleaningTask>
 */
class CleaningTaskFactory extends Factory
{
    public function definition(): array
    {
        $property = Property::factory();

        return [
            'property_id' => $property,
            'reservation_id' => null,
            'status' => CleaningTaskStatus::Pending,
            'cleaning_type' => CleaningType::Checkout,
            'cleaning_fee' => fake()->randomElement([25000, 30000]),
            'scheduled_date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'assigned_to' => fake()->name(),
            'assigned_phone' => '+569'.fake()->numerify('########'),
            'notes' => null,
            'metadata' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(['status' => CleaningTaskStatus::Completed]);
    }

    public function inProgress(): static
    {
        return $this->state(['status' => CleaningTaskStatus::InProgress]);
    }

    public function forReservation(?Reservation $reservation = null): static
    {
        return $this->state(function () use ($reservation) {
            $reservation ??= Reservation::factory()->create();

            return [
                'property_id' => $reservation->property_id,
                'reservation_id' => $reservation->id,
                'scheduled_date' => $reservation->check_out->format('Y-m-d'),
            ];
        });
    }
}
