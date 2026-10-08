<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $issueDate = fake()->dateTimeBetween('-30 days', 'now');
        $dueDate = (clone $issueDate)->modify('+10 days');

        return [
            'property_id' => Property::factory(),
            'tenant_id' => Tenant::factory(),
            'lease_id' => Lease::factory(),
            'invoice_number' => 'INV-'.now()->year.'-'.strtoupper(fake()->unique()->bothify('####')),
            'billing_period' => null,
            'period_start' => null,
            'period_end' => null,
            'issue_date' => $issueDate->format('Y-m-d'),
            'due_date' => $dueDate->format('Y-m-d'),
            'subtotal' => 1500000,
            'discount_amount' => 0,
            'late_fee_amount' => 0,
            'adjustment_amount' => 0,
            'total_amount' => 1500000,
            'amount_paid' => 0,
            'amount_due' => 1500000,
            'status' => InvoiceStatus::Draft,
            'notes' => null,
        ];
    }

    public function issued(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Issued,
            'issued_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Overdue,
            'due_date' => now()->copy()->subDays(5)->toDateString(),
        ]);
    }
}
