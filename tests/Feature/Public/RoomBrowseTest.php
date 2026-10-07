<?php

use App\Enums\RoomStatus;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Property;
use App\Models\Room;

beforeEach(function () {
    $this->property = Property::factory()->create(['name' => 'Kost Mawar']);
    $this->building = Building::factory()->create(['property_id' => $this->property->id]);
    $this->floor = Floor::factory()->create(['building_id' => $this->building->id]);
});

function makeRoom(array $attributes = []): Room
{
    return Room::factory()->create(array_merge([
        'property_id' => test()->property->id,
        'building_id' => test()->building->id,
        'floor_id' => test()->floor->id,
    ], $attributes));
}

it('lists only available rooms on the public rooms page', function () {
    makeRoom(['number' => 'A-101', 'status' => RoomStatus::Available]);
    makeRoom(['number' => 'A-102', 'status' => RoomStatus::Occupied]);

    $this->get(route('rooms'))
        ->assertOk()
        ->assertSee('A-101')
        ->assertDontSee('A-102');
});

it('shows the public home page with available rooms', function () {
    makeRoom(['number' => 'A-201', 'status' => RoomStatus::Available]);
    makeRoom(['number' => 'A-202', 'status' => RoomStatus::Maintenance]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('A-201')
        ->assertDontSee('A-202');
});

it('shows a room detail page for an available room', function () {
    $room = makeRoom(['number' => 'A-301', 'status' => RoomStatus::Available]);

    $this->get(route('rooms.show', $room->slug))
        ->assertOk()
        ->assertSee('A-301');
});

it('returns 404 for an occupied room detail page', function () {
    $room = makeRoom(['number' => 'A-401', 'status' => RoomStatus::Occupied]);

    $this->get(route('rooms.show', $room->slug))->assertNotFound();
});

it('renders the static public pages', function (string $route) {
    $this->get($route)->assertOk();
})->with(['facilities', 'about', 'rules', 'faq', 'contact']);
