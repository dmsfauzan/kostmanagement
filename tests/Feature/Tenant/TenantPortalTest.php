<?php

use App\Models\Building;
use App\Models\Floor;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LeaseService;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->property = Property::factory()->create();
    $building = Building::factory()->create(['property_id' => $this->property->id]);
    $floor = Floor::factory()->create(['building_id' => $building->id]);

    $this->roomA = Room::factory()->create([
        'property_id' => $this->property->id,
        'building_id' => $building->id,
        'floor_id' => $floor->id,
        'number' => 'A-101',
    ]);
    $this->roomB = Room::factory()->create([
        'property_id' => $this->property->id,
        'building_id' => $building->id,
        'floor_id' => $floor->id,
        'number' => 'B-201',
    ]);

    $userA = User::factory()->withRole('tenant')->create(['name' => 'Penghuni A']);
    $userB = User::factory()->withRole('tenant')->create(['name' => 'Penghuni B']);

    $tenantA = Tenant::factory()->create([
        'property_id' => $this->property->id,
        'user_id' => $userA->id,
        'full_name' => 'Penghuni A',
    ]);
    $tenantB = Tenant::factory()->create([
        'property_id' => $this->property->id,
        'user_id' => $userB->id,
        'full_name' => 'Penghuni B',
    ]);

    $this->leaseA = app(LeaseService::class)->create([
        'tenant_id' => $tenantA->id,
        'room_id' => $this->roomA->id,
        'property_id' => $this->property->id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'rent_amount' => 1500000,
        'deposit_amount' => 500000,
        'billing_cycle' => 'monthly',
        'due_day' => 5,
        'late_fee_type' => 'none',
        'late_fee_value' => 0,
    ]);
    app(LeaseService::class)->activate($this->leaseA);

    $leaseB = app(LeaseService::class)->create([
        'tenant_id' => $tenantB->id,
        'room_id' => $this->roomB->id,
        'property_id' => $this->property->id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'rent_amount' => 1500000,
        'deposit_amount' => 500000,
        'billing_cycle' => 'monthly',
        'due_day' => 5,
        'late_fee_type' => 'none',
        'late_fee_value' => 0,
    ]);
    app(LeaseService::class)->activate($leaseB);

    $this->userA = $userA;
});

it('shows the tenant their own room on the dashboard', function () {
    $this->actingAs($this->userA)
        ->get(route('tenant.dashboard'))
        ->assertOk()
        ->assertSee('A-101')
        ->assertDontSee('B-201');
});

it('shows the tenant their own lease and not other tenants', function () {
    $this->actingAs($this->userA)
        ->get(route('tenant.lease'))
        ->assertOk()
        ->assertSee($this->leaseA->code)
        ->assertSee('A-101');
});

it('forbids a tenant from the admin area', function () {
    $this->actingAs($this->userA)
        ->get(route('admin.tenants'))
        ->assertForbidden();
});
