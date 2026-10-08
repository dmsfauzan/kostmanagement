<?php

namespace Database\Factories;

use App\Enums\DepositStatus;
use App\Models\Deposit;
use App\Models\Lease;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deposit>
 */
class DepositFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lease_id' => Lease::factory(),
            'tenant_id' => Tenant::factory(),
            'amount' => 500000,
            'status' => DepositStatus::Held,
            'held_at' => now(),
            'deduction_amount' => 0,
            'refund_amount' => 0,
            'notes' => null,
        ];
    }
}
