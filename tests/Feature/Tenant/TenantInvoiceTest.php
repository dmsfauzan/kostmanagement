<?php

use App\Models\Building;
use App\Models\Floor;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BillingService;
use Carbon\Carbon;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, SettingsSeeder::class]);

    $property = Property::factory()->create();
    $building = Building::factory()->create(['property_id' => $property->id]);
    $floor = Floor::factory()->create(['building_id' => $building->id]);

    $this->userA = User::factory()->withRole('tenant')->create(['name' => 'Penghuni A']);
    $this->userB = User::factory()->withRole('tenant')->create(['name' => 'Penghuni B']);

    $tenantA = Tenant::factory()->create(['property_id' => $property->id, 'user_id' => $this->userA->id]);
    $tenantB = Tenant::factory()->create(['property_id' => $property->id, 'user_id' => $this->userB->id]);

    $roomA = Room::factory()->create(['property_id' => $property->id, 'building_id' => $building->id, 'floor_id' => $floor->id, 'number' => 'A-101']);
    $roomB = Room::factory()->create(['property_id' => $property->id, 'building_id' => $building->id, 'floor_id' => $floor->id, 'number' => 'B-201']);

    $leaseA = Lease::factory()->create(['tenant_id' => $tenantA->id, 'room_id' => $roomA->id, 'property_id' => $property->id, 'rent_amount' => 1500000, 'status' => 'active']);
    $leaseB = Lease::factory()->create(['tenant_id' => $tenantB->id, 'room_id' => $roomB->id, 'property_id' => $property->id, 'rent_amount' => 2000000, 'status' => 'active']);

    $billing = app(BillingService::class);
    $this->invoiceA = $billing->createForLease($leaseA, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'));
    $billing->issue($this->invoiceA);
    $this->invoiceB = $billing->createForLease($leaseB, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-31'));
    $billing->issue($this->invoiceB);
});

it('shows the tenant only their own invoices', function () {
    $this->actingAs($this->userA)
        ->get(route('tenant.invoices'))
        ->assertOk()
        ->assertSee($this->invoiceA->invoice_number)
        ->assertDontSee($this->invoiceB->invoice_number);
});

it('shows a tenant their invoice detail', function () {
    $this->actingAs($this->userA)
        ->get(route('tenant.invoices.show', $this->invoiceA))
        ->assertOk()
        ->assertSee($this->invoiceA->invoice_number);
});

it('blocks a tenant from viewing another tenant invoice', function () {
    $this->actingAs($this->userA)
        ->get(route('tenant.invoices.show', $this->invoiceB))
        ->assertNotFound();
});

it('shows the current invoice on the tenant dashboard', function () {
    $this->actingAs($this->userA)
        ->get(route('tenant.dashboard'))
        ->assertOk()
        ->assertSee($this->invoiceA->invoice_number);
});
