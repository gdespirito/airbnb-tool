<?php

namespace Database\Factories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConversationLog>
 */
class ConversationLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reservation_id' => Reservation::factory(),
            'from_agent' => fake()->randomElement(['alma', 'tita', 'atlas']),
            'contact_name' => fake()->name(),
            'contact_role' => fake()->randomElement(['guest', 'cleaner', 'maintenance', 'owner']),
            'channel' => fake()->randomElement(['whatsapp', 'phone']),
            'summary' => fake()->sentence(),
            'messages' => null,
        ];
    }

    public function withMessages(int $count = 3): static
    {
        return $this->state(fn (array $attributes) => [
            'messages' => collect(range(1, $count))->map(fn () => [
                'from' => fake()->name(),
                'text' => fake()->sentence(),
                'at' => fake()->dateTimeThisMonth()->format('Y-m-d H:i:s'),
            ])->all(),
        ]);
    }
}
