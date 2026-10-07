<?php

use App\Enums\RoomStatus;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Property;
use App\Models\Room;
use App\Services\OccupancyService;

it('counts rooms by status and calculates occupancy', function () {
    $property = Property::factory()->create();
    $building = Building::factory()->create(['property_id' => $property->id]);
    $floor = Floor::factory()->create(['building_id' => $building->id]);

    foreach ([RoomStatus::Occupied, RoomStatus::Occupied, RoomStatus::Available, RoomStatus::Maintenance, RoomStatus::Reserved] as $i => $status) {
        Room::factory()->create([
            'property_id' => $property->id,
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'number' => 'A-10'.$i,
            'status' => $status,
        ]);
    }

    $summary = app(OccupancyService::class)->summary($property->id);

    expect($summary['total'])->toBe(5)
        ->and($summary['occupied'])->toBe(2)
        ->and($summary['available'])->toBe(1)
        ->and($summary['maintenance'])->toBe(1)
        ->and($summary['reserved'])->toBe(1)
        ->and($summary['occupancy_rate'])->toBe(40.0);
});

it('returns zero occupancy when there are no rooms', function () {
    $summary = app(OccupancyService::class)->summary();

    expect($summary['total'])->toBe(0)
        ->and($summary['occupancy_rate'])->toBe(0.0);
});
