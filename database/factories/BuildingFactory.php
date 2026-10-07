<?php

namespace Database\Factories;

use App\Enums\PropertyStatus;
use App\Models\Building;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Building>
 */
class BuildingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'name' => 'Gedung '.fake()->randomLetter(),
            'code' => strtoupper(fake()->unique()->bothify('?#')),
            'floor_count' => fake()->numberBetween(1, 4),
            'description' => fake()->sentence(6),
            'status' => PropertyStatus::Active,
        ];
    }
}
