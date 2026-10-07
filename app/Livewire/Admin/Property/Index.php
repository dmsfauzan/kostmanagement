<?php

namespace App\Livewire\Admin\Property;

use App\Enums\PropertyStatus;
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
    public string $status = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        Gate::authorize('property.update');

        $property = Property::query()->findOrFail($id);

        if ($property->rooms()->exists()) {
            session()->flash('error', 'Properti tidak dapat dihapus karena masih memiliki kamar.');

            return;
        }

        $property->delete();

        session()->flash('status', 'Properti berhasil dihapus.');
    }

    public function render()
    {
        $properties = Property::query()
            ->withCount(['buildings', 'rooms'])
            ->when($this->search, fn ($query) => $query->where(
                fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('city', 'like', "%{$this->search}%")
            ))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.property.index', [
            'properties' => $properties,
            'statuses' => PropertyStatus::cases(),
        ])->layout('components.layouts.admin', ['title' => 'Properti']);
    }
}
