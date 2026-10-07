<?php

namespace Database\Factories;

use App\Enums\RoomStatus;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Property;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'building_id' => Building::factory(),
            'floor_id' => Floor::factory(),
            'room_type_id' => null,
            'number' => strtoupper(fake()->unique()->bothify('?#-###')),
            'slug' => null,
            'price' => fake()->numberBetween(8, 25) * 100000,
            'deposit' => 500000,
            'size_sqm' => fake()->numberBetween(9, 24),
            'description' => fake()->sentence(8),
            'status' => RoomStatus::Available,
        ];
    }

    public function occupied(): static
    {
        return $this->state(fn (array $attributes) => ['status' => RoomStatus::Occupied]);
    }

    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => ['status' => RoomStatus::Maintenance]);
    }
}
