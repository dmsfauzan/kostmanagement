<?php

use App\Livewire\Admin\Amenity\Index;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->owner = User::factory()->withRole('owner')->create();
    $this->actingAs($this->owner);
});

it('renders every admin listing page', function (string $route) {
    $this->get(route($route))->assertOk();
})->with([
    'admin.properties',
    'admin.buildings',
    'admin.floors',
    'admin.room-types',
    'admin.amenities',
    'admin.rooms',
    'admin.rooms.map',
    'admin.tenants',
    'admin.leases',
    'admin.invoices',
    'admin.payments',
    'admin.payment-methods',
]);

it('renders every admin create form', function (string $route) {
    $this->get(route($route))->assertOk();
})->with([
    'admin.properties.create',
    'admin.buildings.create',
    'admin.floors.create',
    'admin.room-types.create',
    'admin.rooms.create',
    'admin.tenants.create',
    'admin.leases.create',
    'admin.invoices.create',
]);

it('renders the edit forms for existing records', function () {
    $property = Property::factory()->create();
    $building = Building::factory()->create(['property_id' => $property->id]);
    $floor = Floor::factory()->create(['building_id' => $building->id]);
    $roomType = RoomType::factory()->create(['property_id' => $property->id]);
    $room = Room::factory()->create([
        'property_id' => $property->id,
        'building_id' => $building->id,
        'floor_id' => $floor->id,
    ]);

    $this->get(route('admin.properties.edit', $property))->assertOk();
    $this->get(route('admin.buildings.edit', $building))->assertOk();
    $this->get(route('admin.floors.edit', $floor))->assertOk();
    $this->get(route('admin.room-types.edit', $roomType))->assertOk();
    $this->get(route('admin.rooms.edit', $room))->assertOk();

    $tenant = Tenant::factory()->create(['property_id' => $property->id]);
    $lease = Lease::factory()->create([
        'tenant_id' => $tenant->id,
        'room_id' => $room->id,
        'property_id' => $property->id,
    ]);

    $this->get(route('admin.tenants.show', $tenant))->assertOk();
    $this->get(route('admin.tenants.edit', $tenant))->assertOk();
    $this->get(route('admin.leases.show', $lease))->assertOk();
    $this->get(route('admin.leases.edit', $lease))->assertOk();

    $invoice = Invoice::factory()->create([
        'property_id' => $property->id,
        'tenant_id' => $tenant->id,
        'lease_id' => $lease->id,
    ]);
    $this->get(route('admin.invoices.show', $invoice->invoice_number))->assertOk();

    $payment = Payment::factory()->create([
        'property_id' => $property->id,
        'invoice_id' => $invoice->id,
        'tenant_id' => $tenant->id,
    ]);
    $this->get(route('admin.payments.show', $payment))->assertOk();
});

it('creates an amenity through the inline form', function () {
    Livewire::test(Index::class)
        ->set('name', 'AC')
        ->set('category', 'kamar')
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('amenities', ['name' => 'AC', 'slug' => 'ac']);
});
