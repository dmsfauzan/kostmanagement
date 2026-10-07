<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RoomType>
 */
class RoomTypeFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Standard', 'Deluxe', 'Premium', 'Ekonomi']);
        $name = $name.' '.fake()->unique()->randomNumber(3);

        return [
            'property_id' => Property::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'default_price' => fake()->numberBetween(8, 25) * 100000,
            'default_deposit' => 500000,
            'size_sqm' => fake()->numberBetween(9, 24),
            'capacity' => 1,
            'description' => fake()->sentence(8),
        ];
    }
}
