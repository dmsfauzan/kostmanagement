<?php

namespace App\Livewire\Admin\Room;

use App\Enums\RoomStatus;
use App\Models\Building;
use App\Models\Property;
use App\Models\Room;
use App\Services\RoomService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $property = '';

    #[Url(except: '')]
    public string $building = '';

    #[Url(except: 'table')]
    public string $view = 'table';

    public function updating(string $field): void
    {
        if (in_array($field, ['search', 'status', 'property', 'building'], true)) {
            $this->resetPage();
        }
    }

    public function setView(string $view): void
    {
        $this->view = $view === 'card' ? 'card' : 'table';
    }

    public function delete(int $id): void
    {
        Gate::authorize('room.delete');

        $room = Room::query()->findOrFail($id);
        app(RoomService::class)->delete($room);

        session()->flash('status', 'Kamar berhasil dihapus.');
    }

    public function render()
    {
        $rooms = Room::query()
            ->with(['property', 'building', 'floor', 'roomType'])
            ->search($this->search)
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->property, fn ($query) => $query->where('property_id', $this->property))
            ->when($this->building, fn ($query) => $query->where('building_id', $this->building))
            ->orderBy('number')
            ->paginate(15);

        return view('livewire.admin.room.index', [
            'rooms' => $rooms,
            'statuses' => RoomStatus::cases(),
            'properties' => Property::query()->orderBy('name')->pluck('name', 'id'),
            'buildings' => Building::query()->orderBy('name')->pluck('name', 'id'),
        ])->layout('components.layouts.admin', ['title' => 'Kamar']);
    }
}
