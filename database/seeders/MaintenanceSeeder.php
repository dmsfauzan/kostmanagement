<?php

namespace Database\Seeders;

use App\Enums\MaintenanceCategory;
use App\Enums\MaintenancePriority;
use App\Models\MaintenanceTicket;
use App\Models\Tenant;
use App\Services\MaintenanceService;
use Illuminate\Database\Seeder;

class MaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('MaintenanceSeeder skipped: not in local/testing environment.');

            return;
        }

        $tenant = Tenant::query()
            ->with(['user', 'property'])
            ->whereHas('user')
            ->first();

        if (! $tenant) {
            $this->command?->warn('MaintenanceSeeder skipped: no tenant with user.');

            return;
        }

        $existing = MaintenanceTicket::query()
            ->where('tenant_id', $tenant->id)
            ->where('title', 'AC tidak dingin')
            ->first();

        if ($existing) {
            $this->command?->info('MaintenanceSeeder skipped: example ticket already exists.');

            return;
        }

        $service = app(MaintenanceService::class);

        $ticket = $service->create($tenant, [
            'title' => 'AC tidak dingin',
            'category' => MaintenanceCategory::Ac->value,
            'description' => 'AC di kamar tidak dingin sejak kemarin. Suhu kamar terasa panas.',
            'priority' => MaintenancePriority::High->value,
        ]);

        $service->addComment($ticket, $tenant->user, 'Mohon segera ditindaklanjuti, terima kasih.', false);

        $this->command?->info('MaintenanceSeeder: ticket '.$ticket->ticket_number.' created.');
    }
}
