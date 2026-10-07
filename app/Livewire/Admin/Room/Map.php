<?php

namespace App\Livewire\Admin\Room;

use App\Models\Property;
use App\Models\Room;
use App\Services\OccupancyService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;

class Map extends Component
{
    #[Url(except: '')]
    public string $property = '';

    public function mount(): void
    {
        Gate::authorize('room.view');
    }

    public function render()
    {
        $rooms = Room::query()
            ->with(['building', 'floor', 'roomType'])
            ->when($this->property, fn ($query) => $query->where('property_id', $this->property))
            ->orderBy('number')
            ->get();

        $buildings = $rooms
            ->groupBy(fn (Room $room) => $room->building->name ?? 'Tanpa Gedung')
            ->map(fn ($buildingRooms) => $buildingRooms
                ->groupBy(fn (Room $room) => $room->floor->name ?? 'Tanpa Lantai')
            );

        return view('livewire.admin.room.map', [
            'buildings' => $buildings,
            'properties' => Property::query()->orderBy('name')->pluck('name', 'id'),
            'occupancy' => app(OccupancyService::class)->summary($this->property ? (int) $this->property : null),
        ])->layout('components.layouts.admin', ['title' => 'Peta Kamar']);
    }
}
