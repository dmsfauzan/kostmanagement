<?php

namespace Database\Factories;

use App\Enums\MaintenanceCategory;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Models\MaintenanceTicket;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceTicket>
 */
class MaintenanceTicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'room_id' => null,
            'tenant_id' => null,
            'ticket_number' => 'MNT-'.now()->year.'-'.strtoupper(fake()->unique()->bothify('####')),
            'title' => fake()->sentence(4),
            'category' => fake()->randomElement(MaintenanceCategory::cases()),
            'description' => fake()->paragraph(2),
            'priority' => fake()->randomElement(MaintenancePriority::cases()),
            'status' => MaintenanceStatus::Submitted,
            'assigned_to' => null,
            'sla_due_at' => now()->addHours(48),
            'created_by' => null,
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MaintenanceStatus::Resolved,
            'resolved_at' => now(),
        ]);
    }
}
