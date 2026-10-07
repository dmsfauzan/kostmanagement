<?php

namespace App\Livewire\Admin\RoomType;

use App\Models\Property;
use App\Models\RoomType;
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
        Gate::authorize('room_type.delete');

        $type = RoomType::query()->findOrFail($id);

        if ($type->rooms()->exists()) {
            session()->flash('error', 'Tipe kamar tidak dapat dihapus karena masih digunakan oleh kamar.');

            return;
        }

        $type->delete();
        session()->flash('status', 'Tipe kamar berhasil dihapus.');
    }

    public function render()
    {
        $types = RoomType::query()
            ->with('property')
            ->withCount('rooms')
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->when($this->property, fn ($query) => $query->where('property_id', $this->property))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.room-type.index', [
            'types' => $types,
            'properties' => Property::query()->orderBy('name')->pluck('name', 'id'),
        ])->layout('components.layouts.admin', ['title' => 'Tipe Kamar']);
    }
}
