<?php

namespace App\Services;

use App\Models\Floor;
use App\Models\Room;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RoomService
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>  $amenityIds
     * @param  list<UploadedFile>  $photos
     */
    public function create(array $data, array $amenityIds = [], array $photos = []): Room
    {
        $this->assertFloorMatchesBuilding($data);

        $room = DB::transaction(function () use ($data, $amenityIds): Room {
            $room = Room::query()->create($data);

            if ($amenityIds !== []) {
                $room->amenities()->sync($amenityIds);
            }

            return $room;
        });

        $this->storePhotos($room, $photos);

        $this->audit->record('room.created', $room, [], $room->only([
            'number', 'price', 'deposit', 'status',
        ]), 'Room');

        return $room;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>  $amenityIds
     * @param  list<UploadedFile>  $photos
     */
    public function update(Room $room, array $data, array $amenityIds = [], array $photos = []): Room
    {
        $this->assertFloorMatchesBuilding($data);

        $before = $room->only(['number', 'price', 'deposit', 'status']);
        $priceChanged = array_key_exists('price', $data) && (int) $data['price'] !== $room->price;

        DB::transaction(function () use ($room, $data, $amenityIds): void {
            $room->update($data);
            $room->amenities()->sync($amenityIds);
        });

        $this->storePhotos($room, $photos);

        $this->audit->record('room.updated', $room, $before, $room->only([
            'number', 'price', 'deposit', 'status',
        ]), 'Room');

        if ($priceChanged) {
            $this->audit->record('room.price_changed', $room, [
                'price' => $before['price'],
            ], [
                'price' => $room->price,
            ], 'Room');
        }

        return $room;
    }

    public function delete(Room $room): void
    {
        $this->audit->record('room.deleted', $room, $room->only(['number', 'price', 'status']), [], 'Room');

        $room->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertFloorMatchesBuilding(array $data): void
    {
        if (! isset($data['floor_id'], $data['building_id'])) {
            return;
        }

        $floor = Floor::query()->find($data['floor_id']);

        if ($floor && (int) $floor->building_id !== (int) $data['building_id']) {
            throw new InvalidArgumentException('Lantai tidak sesuai dengan gedung yang dipilih.');
        }
    }

    /**
     * @param  list<UploadedFile>  $photos
     */
    private function storePhotos(Room $room, array $photos): void
    {
        if ($photos === []) {
            return;
        }

        $disk = config('kost.disk.public');
        $sortOrder = (int) $room->photos()->max('sort_order');

        foreach ($photos as $photo) {
            $path = $photo->store("rooms/{$room->id}", $disk);

            $room->photos()->create([
                'path' => $path,
                'sort_order' => ++$sortOrder,
            ]);
        }
    }
}
