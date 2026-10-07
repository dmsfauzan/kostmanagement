<?php

namespace Database\Seeders;

use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Models\Amenity;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $owner = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'owner'))->first()
            ?? User::query()->first();

        $amenities = collect([
            'AC' => 'kamar',
            'WiFi' => 'umum',
            'Kamar Mandi Dalam' => 'kamar',
            'Kasur' => 'kamar',
            'Lemari' => 'kamar',
            'Meja Belajar' => 'kamar',
            'Air Panas' => 'kamar',
            'Parkir' => 'umum',
            'Dapur Bersama' => 'umum',
            'Laundry' => 'umum',
            'CCTV' => 'keamanan',
        ])->map(fn (string $category, string $name) => Amenity::query()->firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'category' => $category, 'is_active' => true],
        ));

        $property = Property::query()->firstOrCreate(
            ['slug' => 'kost-mawar'],
            [
                'owner_id' => $owner?->id,
                'name' => 'Kost Mawar',
                'address' => 'Jl. Melati No. 12',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'postal_code' => '12140',
                'phone' => '021-12345678',
                'email' => 'info@kostmawar.test',
                'description' => 'Kost nyaman dengan lokasi strategis dekat kampus dan perkantoran.',
                'status' => PropertyStatus::Active,
            ],
        );

        $types = [
            'Standard' => [1500000, 500000, 12],
            'Deluxe' => [2000000, 750000, 16],
            'Premium' => [2500000, 1000000, 20],
        ];

        $roomTypes = collect($types)->mapWithKeys(function (array $config, string $name) use ($property, $amenities) {
            [$price, $deposit, $size] = $config;

            $type = RoomType::query()->firstOrCreate(
                ['property_id' => $property->id, 'slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'default_price' => $price,
                    'default_deposit' => $deposit,
                    'size_sqm' => $size,
                    'capacity' => 1,
                    'description' => "Kamar tipe {$name}.",
                ],
            );

            $type->amenities()->sync($amenities->take(3)->pluck('id')->all());

            return [$name => $type];
        });

        $buildings = [
            'A' => ['Gedung A', ['Lantai 1' => 1, 'Lantai 2' => 2]],
            'B' => ['Gedung B', ['Lantai 1' => 1]],
        ];

        $statuses = [
            RoomStatus::Available, RoomStatus::Available, RoomStatus::Available,
            RoomStatus::Occupied, RoomStatus::Occupied,
            RoomStatus::Maintenance, RoomStatus::Reserved,
        ];

        $statusIndex = 0;

        foreach ($buildings as $code => [$buildingName, $floorConfig]) {
            $building = Building::query()->firstOrCreate(
                ['property_id' => $property->id, 'code' => $code],
                ['name' => $buildingName, 'floor_count' => count($floorConfig), 'status' => PropertyStatus::Active],
            );

            foreach ($floorConfig as $floorName => $level) {
                $floor = Floor::query()->firstOrCreate(
                    ['building_id' => $building->id, 'level' => $level],
                    ['name' => $floorName],
                );

                foreach (range(1, 10) as $index) {
                    $number = sprintf('%s-%d%02d', $code, $level, $index);

                    if (Room::query()->where('property_id', $property->id)->where('number', $number)->exists()) {
                        continue;
                    }

                    $typeName = match (true) {
                        $index <= 5 => 'Standard',
                        $index <= 8 => 'Deluxe',
                        default => 'Premium',
                    };
                    $type = $roomTypes[$typeName];

                    $room = Room::query()->create([
                        'property_id' => $property->id,
                        'building_id' => $building->id,
                        'floor_id' => $floor->id,
                        'room_type_id' => $type->id,
                        'number' => $number,
                        'price' => $type->default_price,
                        'deposit' => $type->default_deposit,
                        'size_sqm' => $type->size_sqm,
                        'description' => "Kamar {$number} tipe {$typeName}.",
                        'status' => $statuses[$statusIndex % count($statuses)],
                    ]);

                    $statusIndex++;

                    $room->amenities()->sync($amenities->shuffle()->take(random_int(3, 6))->pluck('id')->all());
                }
            }
        }
    }
}
