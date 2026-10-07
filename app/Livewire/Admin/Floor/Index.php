<?php

namespace App\Livewire\Admin\Floor;

use App\Models\Building;
use App\Models\Floor;
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
    public string $building = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingBuilding(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        Gate::authorize('floor.delete');

        $floor = Floor::query()->withCount('rooms')->findOrFail($id);

        if ($floor->rooms_count > 0) {
            session()->flash('error', 'Lantai tidak dapat dihapus karena masih memiliki kamar.');

            return;
        }

        $floor->delete();
        session()->flash('status', 'Lantai berhasil dihapus.');
    }

    public function render()
    {
        $floors = Floor::query()
            ->with('building')
            ->withCount('rooms')
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->when($this->building, fn ($query) => $query->where('building_id', $this->building))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.floor.index', [
            'floors' => $floors,
            'buildings' => Building::query()->with('property')->get(),
        ])->layout('components.layouts.admin', ['title' => 'Lantai']);
    }
}
