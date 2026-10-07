<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\Floor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Floor>
 */
class FloorFactory extends Factory
{
    public function definition(): array
    {
        static $level = 0;

        return [
            'building_id' => Building::factory(),
            'name' => 'Lantai '.fake()->numberBetween(1, 4),
            'level' => ++$level,
            'description' => null,
        ];
    }
}
