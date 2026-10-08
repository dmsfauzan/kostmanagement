<?php

use App\Models\Building;
use App\Models\Floor;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BillingService;
use Carbon\Carbon;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, SettingsSeeder::class, PaymentMethodSeeder::class]);

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

it('shows a tenant only their own payments', function () {
    $own = Payment::factory()->create([
        'property_id' => $this->invoiceA->property_id,
        'invoice_id' => $this->invoiceA->id,
        'tenant_id' => $this->invoiceA->tenant_id,
        'amount' => 500000,
    ]);
    $other = Payment::factory()->create([
        'property_id' => $this->invoiceB->property_id,
        'invoice_id' => $this->invoiceB->id,
        'tenant_id' => $this->invoiceB->tenant_id,
        'amount' => 900000,
    ]);

    $this->actingAs($this->userA)
        ->get(route('tenant.payments'))
        ->assertOk()
        ->assertSee('Rp 500.000')
        ->assertDontSee('Rp 900.000');
});

it('blocks a tenant from admin payments', function () {
    $this->actingAs($this->userA)
        ->get(route('admin.payments'))
        ->assertForbidden();
});
