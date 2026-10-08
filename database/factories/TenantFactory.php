<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Enums\TenantStatus;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => null,
            'user_id' => null,
            'full_name' => fake()->name(),
            'nik' => fake()->unique()->numerify('################'),
            'gender' => fake()->randomElement(Gender::cases()),
            'birth_date' => fake()->dateTimeBetween('-40 years', '-18 years')->format('Y-m-d'),
            'phone' => '08'.fake()->numerify('##########'),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->address(),
            'emergency_name' => fake()->name(),
            'emergency_relationship' => fake()->randomElement(['Orang Tua', 'Saudara', 'Pasangan']),
            'emergency_phone' => '08'.fake()->numerify('##########'),
            'vehicle_type' => fake()->randomElement(['Motor', 'Mobil', null]),
            'vehicle_number' => strtoupper(fake()->bothify('? #### ??')),
            'notes' => null,
            'status' => TenantStatus::Prospect,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => ['status' => TenantStatus::Active]);
    }
}
