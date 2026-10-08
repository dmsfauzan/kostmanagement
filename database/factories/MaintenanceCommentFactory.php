<?php

namespace Database\Factories;

use App\Models\MaintenanceComment;
use App\Models\MaintenanceTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceComment>
 */
class MaintenanceCommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_id' => MaintenanceTicket::factory(),
            'user_id' => User::factory(),
            'body' => fake()->sentence(8),
            'is_internal' => false,
        ];
    }

    public function internal(): static
    {
        return $this->state(fn (array $attributes) => ['is_internal' => true]);
    }
}
