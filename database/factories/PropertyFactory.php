<?php

namespace Database\Factories;

use App\Enums\PropertyStatus;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Kost '.fake()->unique()->streetName();

        return [
            'owner_id' => null,
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'address' => fake()->streetAddress(),
            'city' => fake()->randomElement(['Jakarta Selatan', 'Bandung', 'Yogyakarta', 'Surabaya']),
            'province' => fake()->randomElement(['DKI Jakarta', 'Jawa Barat', 'DI Yogyakarta', 'Jawa Timur']),
            'postal_code' => fake()->postcode(),
            'phone' => '08'.fake()->numerify('##########'),
            'email' => fake()->unique()->companyEmail(),
            'description' => fake()->sentence(12),
            'status' => PropertyStatus::Active,
        ];
    }
}
