<?php

use App\Enums\DepositStatus;
use App\Enums\LeaseStatus;
use App\Enums\RoomStatus;
use App\Livewire\Admin\Lease\Form as LeaseForm;
use App\Livewire\Admin\Lease\MoveOut as LeaseMoveOut;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\DepositService;
use App\Services\LeaseService;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->owner = User::factory()->withRole('owner')->create();
    $this->actingAs($this->owner);

    $this->property = Property::factory()->create();
    $this->building = Building::factory()->create(['property_id' => $this->property->id]);
    $this->floor = Floor::factory()->create(['building_id' => $this->building->id]);
    $this->room = Room::factory()->create([
        'property_id' => $this->property->id,
        'building_id' => $this->building->id,
        'floor_id' => $this->floor->id,
        'number' => 'A-101',
        'price' => 1500000,
        'deposit' => 500000,
        'status' => RoomStatus::Available,
    ]);
    $this->tenant = Tenant::factory()->create(['property_id' => $this->property->id]);
});

function leaseData(array $overrides = []): array
{
    return array_merge([
        'tenant_id' => test()->tenant->id,
        'room_id' => test()->room->id,
        'property_id' => test()->property->id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'rent_amount' => 1500000,
        'deposit_amount' => 500000,
        'billing_cycle' => 'monthly',
        'due_day' => 5,
        'late_fee_type' => 'none',
        'late_fee_value' => 0,
    ], $overrides);
}

it('creates a draft lease through the form', function () {
    Livewire::test(LeaseForm::class)
        ->set('property_id', $this->property->id)
        ->set('tenant_id', $this->tenant->id)
        ->set('room_id', $this->room->id)
        ->set('start_date', '2026-01-01')
        ->set('end_date', '2026-12-31')
        ->set('rent_amount', 1500000)
        ->set('deposit_amount', 500000)
        ->set('billing_cycle', 'monthly')
        ->set('due_day', 5)
        ->set('late_fee_type', 'none')
        ->call('save')
        ->assertHasNoErrors();

    $lease = Lease::query()->first();
    expect($lease)->not->toBeNull()
        ->and($lease->status)->toBe(LeaseStatus::Draft)
        ->and($lease->code)->toStartWith('LSE-');
});

it('rejects an end date before the start date', function () {
    Livewire::test(LeaseForm::class)
        ->set('property_id', $this->property->id)
        ->set('tenant_id', $this->tenant->id)
        ->set('room_id', $this->room->id)
        ->set('start_date', '2026-06-01')
        ->set('end_date', '2026-01-01')
        ->set('rent_amount', 1500000)
        ->set('due_day', 5)
        ->call('save')
        ->assertHasErrors(['end_date']);
});

it('activates a lease, occupies the room and holds the deposit', function () {
    $lease = app(LeaseService::class)->create(leaseData());
    app(LeaseService::class)->activate($lease);

    $lease->refresh();
    $this->room->refresh();

    expect($lease->status)->toBe(LeaseStatus::Active)
        ->and($this->room->status)->toBe(RoomStatus::Occupied)
        ->and($lease->deposit)->not->toBeNull()
        ->and($lease->deposit->status)->toBe(DepositStatus::Held)
        ->and($lease->statusHistories()->count())->toBeGreaterThanOrEqual(2);
});

it('prevents activating an overlapping lease for the same room', function () {
    $first = app(LeaseService::class)->create(leaseData());
    app(LeaseService::class)->activate($first);

    $otherTenant = Tenant::factory()->create(['property_id' => $this->property->id]);
    $second = app(LeaseService::class)->create(leaseData([
        'tenant_id' => $otherTenant->id,
        'start_date' => '2026-03-01',
        'end_date' => '2027-02-28',
    ]));

    expect(fn () => app(LeaseService::class)->activate($second))
        ->toThrow(InvalidArgumentException::class);

    expect($second->refresh()->status)->toBe(LeaseStatus::Draft);
});

it('prevents the same tenant from having overlapping leases', function () {
    $first = app(LeaseService::class)->create(leaseData());
    app(LeaseService::class)->activate($first);

    $anotherRoom = Room::factory()->create([
        'property_id' => $this->property->id,
        'building_id' => $this->building->id,
        'floor_id' => $this->floor->id,
        'number' => 'A-102',
    ]);

    $second = app(LeaseService::class)->create(leaseData([
        'room_id' => $anotherRoom->id,
        'start_date' => '2026-06-01',
        'end_date' => '2027-05-31',
    ]));

    expect(fn () => app(LeaseService::class)->activate($second))
        ->toThrow(InvalidArgumentException::class);
});

it('terminates a lease and frees the room', function () {
    $lease = app(LeaseService::class)->create(leaseData());
    app(LeaseService::class)->activate($lease);

    app(LeaseService::class)->terminate($lease->refresh(), 'Kontrak berakhir');

    expect($lease->refresh()->status)->toBe(LeaseStatus::Terminated)
        ->and($this->room->refresh()->status)->toBe(RoomStatus::Available);
});

it('settles the deposit during move-out through the component', function () {
    $lease = app(LeaseService::class)->create(leaseData());
    app(LeaseService::class)->activate($lease);

    Livewire::test(LeaseMoveOut::class, ['lease' => $lease->refresh()])
        ->set('termination_reason', 'Kontrak selesai')
        ->set('deduction', 100000)
        ->set('refund', 400000)
        ->call('process')
        ->assertHasNoErrors();

    $lease->refresh();
    expect($lease->status)->toBe(LeaseStatus::Terminated)
        ->and($lease->deposit->status)->toBe(DepositStatus::PartiallyReturned)
        ->and($this->room->refresh()->status)->toBe(RoomStatus::Available);
});

it('rejects deposit settlement greater than the deposit amount', function () {
    $lease = app(LeaseService::class)->create(leaseData());
    app(LeaseService::class)->activate($lease);

    expect(fn () => app(DepositService::class)->settle($lease->refresh(), 300000, 300000))
        ->toThrow(InvalidArgumentException::class);
});

it('forbids technicians from creating leases', function () {
    $technician = User::factory()->withRole('technician')->create();

    $this->actingAs($technician)
        ->get(route('admin.leases.create'))
        ->assertForbidden();
});
