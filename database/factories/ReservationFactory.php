<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('now', '+3 months');
        $nights = fake()->numberBetween(2, 7);
        $checkOut = (clone $checkIn)->modify("+{$nights} days");

        return [
            'property_id' => Property::factory(),
            'airbnb_reservation_id' => null,
            'guest_name' => fake()->name(),
            'guest_phone' => '+569'.fake()->numerify('########'),
            'guest_email' => fake()->safeEmail(),
            'number_of_guests' => fake()->numberBetween(1, 6),
            'check_in' => $checkIn->format('Y-m-d'),
            'check_out' => $checkOut->format('Y-m-d'),
            'status' => ReservationStatus::Confirmed,
            'notes' => null,
            'source' => 'manual',
            'metadata' => null,
        ];
    }

    public function checkedIn(): static
    {
        return $this->state(['status' => ReservationStatus::CheckedIn]);
    }

    public function checkedOut(): static
    {
        return $this->state(['status' => ReservationStatus::CheckedOut]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => ReservationStatus::Cancelled]);
    }

    public function past(): static
    {
        return $this->state(function () {
            $checkIn = fake()->dateTimeBetween('-3 months', '-1 week');
            $checkOut = (clone $checkIn)->modify('+'.fake()->numberBetween(2, 7).' days');

            return [
                'check_in' => $checkIn->format('Y-m-d'),
                'check_out' => $checkOut->format('Y-m-d'),
                'status' => ReservationStatus::CheckedOut,
            ];
        });
    }
}
