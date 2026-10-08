<?php

namespace Database\Factories;

use App\Enums\BillingCycle;
use App\Enums\LateFeeType;
use App\Enums\LeaseStatus;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lease>
 */
class LeaseFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-2 months', 'now');

        return [
            'tenant_id' => Tenant::factory(),
            'room_id' => Room::factory(),
            'property_id' => Property::factory(),
            'code' => 'LSE-'.now()->year.'-'.strtoupper(fake()->unique()->bothify('####')),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => (clone $start)->modify('+1 year')->format('Y-m-d'),
            'rent_amount' => fake()->numberBetween(8, 25) * 100000,
            'deposit_amount' => 500000,
            'billing_cycle' => BillingCycle::Monthly,
            'due_day' => 1,
            'late_fee_type' => LateFeeType::None,
            'late_fee_value' => 0,
            'status' => LeaseStatus::Draft,
            'notes' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeaseStatus::Active,
            'signed_at' => now(),
        ]);
    }
}
