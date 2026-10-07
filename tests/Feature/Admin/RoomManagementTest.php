<?php

use App\Enums\RoomStatus;
use App\Livewire\Admin\Room\Form as RoomForm;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->owner = User::factory()->withRole('owner')->create();
    $this->property = Property::factory()->create();
    $this->building = Building::factory()->create(['property_id' => $this->property->id]);
    $this->floor = Floor::factory()->create(['building_id' => $this->building->id]);
});

function roomPayload(int $propertyId, int $buildingId, int $floorId, string $number = 'A-101'): array
{
    return [
        'property_id' => $propertyId,
        'building_id' => $buildingId,
        'floor_id' => $floorId,
        'number' => $number,
        'price' => 1500000,
        'deposit' => 500000,
        'status' => 'available',
    ];
}

it('creates a room through the livewire form', function () {
    $this->actingAs($this->owner);

    Livewire::test(RoomForm::class)
        ->set('property_id', $this->property->id)
        ->set('building_id', $this->building->id)
        ->set('floor_id', $this->floor->id)
        ->set('number', 'A-101')
        ->set('price', 1500000)
        ->set('deposit', 500000)
        ->set('status', 'available')
        ->call('save')
        ->assertHasNoErrors();

    expect(Room::query()->where('number', 'A-101')->exists())->toBeTrue();
});

it('rejects a duplicate room number within the same property', function () {
    Room::factory()->create([
        'property_id' => $this->property->id,
        'building_id' => $this->building->id,
        'floor_id' => $this->floor->id,
        'number' => 'A-101',
    ]);

    $this->actingAs($this->owner);

    Livewire::test(RoomForm::class)
        ->set('property_id', $this->property->id)
        ->set('building_id', $this->building->id)
        ->set('floor_id', $this->floor->id)
        ->set('number', 'A-101')
        ->set('price', 1500000)
        ->set('status', 'available')
        ->call('save')
        ->assertHasErrors(['number']);
});

it('rejects a floor that does not belong to the chosen building', function () {
    $otherFloor = Floor::factory()->create(['building_id' => Building::factory()->create(['property_id' => $this->property->id])->id]);

    $this->actingAs($this->owner);

    Livewire::test(RoomForm::class)
        ->set('property_id', $this->property->id)
        ->set('building_id', $this->building->id)
        ->set('floor_id', $otherFloor->id)
        ->set('number', 'A-999')
        ->set('price', 1500000)
        ->set('status', 'available')
        ->call('save')
        ->assertHasErrors(['floor_id']);
});

it('rejects a negative price', function () {
    $this->actingAs($this->owner);

    Livewire::test(RoomForm::class)
        ->set('property_id', $this->property->id)
        ->set('building_id', $this->building->id)
        ->set('floor_id', $this->floor->id)
        ->set('number', 'A-555')
        ->set('price', -1)
        ->set('status', 'available')
        ->call('save')
        ->assertHasErrors(['price']);
});

it('records an audit log when a room is created', function () {
    $this->actingAs($this->owner);

    Livewire::test(RoomForm::class)
        ->set('property_id', $this->property->id)
        ->set('building_id', $this->building->id)
        ->set('floor_id', $this->floor->id)
        ->set('number', 'A-777')
        ->set('price', 1000000)
        ->set('status', 'available')
        ->call('save');

    $this->assertDatabaseHas('audit_logs', ['action' => 'room.created', 'actor_id' => $this->owner->id]);
});

it('forbids a technician from creating rooms', function () {
    $technician = User::factory()->withRole('technician')->create();

    $this->actingAs($technician)
        ->get(route('admin.rooms.create'))
        ->assertForbidden();
});

it('forbids a tenant from the admin room list', function () {
    $tenant = User::factory()->withRole('tenant')->create();

    $this->actingAs($tenant)
        ->get(route('admin.rooms'))
        ->assertForbidden();
});

it('renders the room map for an owner', function () {
    Room::factory()->create([
        'property_id' => $this->property->id,
        'building_id' => $this->building->id,
        'floor_id' => $this->floor->id,
        'number' => 'A-101',
        'status' => RoomStatus::Available,
    ]);

    $this->actingAs($this->owner)
        ->get(route('admin.rooms.map'))
        ->assertOk()
        ->assertSee('Peta Kamar')
        ->assertSee('A-101');
});
