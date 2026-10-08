<?php

namespace Database\Factories;

use App\Enums\AnnouncementStatus;
use App\Enums\AnnouncementTarget;
use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => null,
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(2),
            'target_type' => AnnouncementTarget::All,
            'target_id' => null,
            'publish_at' => null,
            'expires_at' => null,
            'status' => AnnouncementStatus::Draft,
            'created_by' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AnnouncementStatus::Published,
            'publish_at' => now(),
        ]);
    }
}
