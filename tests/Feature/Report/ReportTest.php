<?php

use App\Models\Building;
use App\Models\Floor;
use App\Models\Lease;
use App\Models\MaintenanceTicket;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BillingService;
use App\Services\ReportService;
use Carbon\Carbon;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, SettingsSeeder::class, PaymentMethodSeeder::class]);

    $this->owner = User::factory()->withRole('owner')->create();
    $this->actingAs($this->owner);

    $this->property = Property::factory()->create();
    $building = Building::factory()->create(['property_id' => $this->property->id]);
    $floor = Floor::factory()->create(['building_id' => $building->id]);
    $this->room = Room::factory()->create(['property_id' => $this->property->id, 'building_id' => $building->id, 'floor_id' => $floor->id, 'number' => 'A-101']);
    $this->tenant = Tenant::factory()->create(['property_id' => $this->property->id, 'full_name' => 'Budi Santoso']);
    $this->lease = Lease::factory()->create(['tenant_id' => $this->tenant->id, 'room_id' => $this->room->id, 'property_id' => $this->property->id, 'status' => 'active']);
});

it('reports occupancy rows for every room', function () {
    $report = app(ReportService::class)->report('occupancy', ['property' => $this->property->id]);

    expect($report['title'])->toBe('Laporan Okupansi Kamar')
        ->and($report['rows'])->toHaveCount(1)
        ->and($report['rows'][0][0])->toBe('A-101');
});

it('reports revenue from invoices in range', function () {
    $billing = app(BillingService::class);
    $invoice = $billing->createForLease($this->lease->refresh(), Carbon::now()->copy()->firstOfMonth(), Carbon::now()->copy()->endOfMonth()->startOfDay());
    $billing->issue($invoice);

    $report = app(ReportService::class)->report('revenue', ['property' => $this->property->id]);

    expect($report['rows'])->toHaveCount(1)
        ->and($report['rows'][0][0])->toBe($invoice->invoice_number);
});

it('exports csv, xlsx and pdf of a report', function () {
    MaintenanceTicket::factory()->create([
        'property_id' => $this->property->id,
        'tenant_id' => $this->tenant->id,
        'room_id' => $this->room->id,
        'title' => 'AC bocor',
    ]);

    $this->get(route('admin.reports.export', ['type' => 'maintenance', 'format' => 'csv']))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $this->get(route('admin.reports.export', ['type' => 'maintenance', 'format' => 'xlsx']))
        ->assertOk();

    $this->get(route('admin.reports.export', ['type' => 'maintenance', 'format' => 'pdf']))
        ->assertOk();
});

it('renders the reports hub', function () {
    $this->get(route('admin.reports'))->assertOk()->assertSee('Laporan');
});
