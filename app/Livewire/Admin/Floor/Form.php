<?php

namespace App\Livewire\Admin\Floor;

use App\Models\Building;
use App\Models\Floor;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
    public ?Floor $floor = null;

    public ?int $building_id = null;

    public string $name = '';

    public ?int $level = null;

    public ?string $description = '';

    public function mount(?Floor $floor = null): void
    {
        if ($floor && $floor->exists) {
            Gate::authorize('floor.update');
            $this->floor = $floor;
            $this->building_id = $floor->building_id;
            $this->name = $floor->name;
            $this->level = $floor->level;
            $this->description = $floor->description;
        } else {
            Gate::authorize('floor.create');
            $this->building_id = Building::query()->value('id');
            $this->level = Floor::query()->max('level') !== null ? Floor::query()->max('level') + 1 : 1;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'building_id' => ['required', 'exists:buildings,id'],
            'name' => ['required', 'string', 'max:150'],
            'level' => [
                'required', 'integer', 'min:-10', 'max:100',
                Rule::unique('floors', 'level')
                    ->where('building_id', $this->building_id)
                    ->ignore($this->floor?->id),
            ],
            'description' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->floor) {
            $this->floor->update($data);
            session()->flash('status', 'Lantai berhasil diperbarui.');
        } else {
            Floor::query()->create($data);
            session()->flash('status', 'Lantai berhasil ditambahkan.');
        }

        $this->redirectRoute('admin.floors', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.floor.form', [
            'buildings' => Building::query()->with('property')->get(),
        ])->layout('components.layouts.admin', [
            'title' => $this->floor ? 'Ubah Lantai' : 'Tambah Lantai',
        ]);
    }
}
