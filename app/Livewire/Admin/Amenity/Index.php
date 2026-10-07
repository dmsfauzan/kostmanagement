<?php

namespace App\Livewire\Admin\Amenity;

use App\Models\Amenity;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public string $name = '';

    public ?string $category = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        Gate::authorize('amenity.create');

        $this->validate([
            'name' => ['required', 'string', 'max:80'],
            'category' => ['nullable', 'string', 'max:40'],
        ]);

        Amenity::query()->create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'category' => $this->category ?: null,
            'is_active' => true,
        ]);

        $this->reset(['name', 'category']);
        session()->flash('status', 'Fasilitas berhasil ditambahkan.');
    }

    public function delete(int $id): void
    {
        Gate::authorize('amenity.delete');
        Amenity::query()->findOrFail($id)->delete();
        session()->flash('status', 'Fasilitas berhasil dihapus.');
    }

    public function render()
    {
        $amenities = Amenity::query()
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.amenity.index', [
            'amenities' => $amenities,
        ])->layout('components.layouts.admin', ['title' => 'Fasilitas']);
    }
}
