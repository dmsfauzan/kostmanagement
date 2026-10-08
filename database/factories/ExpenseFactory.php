<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'expense_category_id' => ExpenseCategory::factory(),
            'amount' => fake()->numberBetween(50, 2000) * 1000,
            'expense_date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'vendor' => fake()->company(),
            'description' => fake()->sentence(6),
            'receipt_path' => null,
            'created_by' => null,
        ];
    }
}
