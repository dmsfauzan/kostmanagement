<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Property;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'invoice_id' => Invoice::factory(),
            'tenant_id' => Tenant::factory(),
            'amount' => 1500000,
            'paid_at' => now()->toDateString(),
            'payment_method_id' => PaymentMethod::factory(),
            'reference' => strtoupper(fake()->bothify('TRX-####??')),
            'proof_path' => null,
            'notes' => null,
            'status' => PaymentStatus::Pending,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Verified,
            'verified_at' => now(),
        ]);
    }
}
