<?php

namespace App\Livewire\Admin\Building;

use App\Models\Building;
use App\Models\Property;
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
    public string $property = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingProperty(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        Gate::authorize('building.delete');

        $building = Building::query()->withCount(['floors', 'rooms'])->findOrFail($id);

        if ($building->floors_count > 0 || $building->rooms_count > 0) {
            session()->flash('error', 'Gedung tidak dapat dihapus karena masih memiliki lantai atau kamar.');

            return;
        }

        $building->delete();
        session()->flash('status', 'Gedung berhasil dihapus.');
    }

    public function render()
    {
        $buildings = Building::query()
            ->with('property')
            ->withCount(['floors', 'rooms'])
            ->when($this->search, fn ($query) => $query->where(
                fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%")
            ))
            ->when($this->property, fn ($query) => $query->where('property_id', $this->property))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.building.index', [
            'buildings' => $buildings,
            'properties' => Property::query()->orderBy('name')->pluck('name', 'id'),
        ])->layout('components.layouts.admin', ['title' => 'Gedung']);
    }
}
