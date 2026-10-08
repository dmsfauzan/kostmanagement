<?php

namespace Database\Factories;

use App\Enums\PaymentMethodType;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => Str::title($name),
            'code' => Str::slug($name),
            'type' => PaymentMethodType::Transfer,
            'account_name' => fake()->name(),
            'account_number' => fake()->bankAccountNumber(),
            'instructions' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
