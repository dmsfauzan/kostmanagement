<?php

namespace App\Livewire\Admin\Building;

use App\Enums\PropertyStatus;
use App\Models\Building;
use App\Models\Property;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
    public ?Building $building = null;

    public ?int $property_id = null;

    public string $name = '';

    public ?string $code = '';

    public ?int $floor_count = null;

    public ?string $description = '';

    public string $status = 'active';

    public function mount(?Building $building = null): void
    {
        if ($building && $building->exists) {
            Gate::authorize('building.update');
            $this->building = $building;
            $this->property_id = $building->property_id;
            $this->name = $building->name;
            $this->code = $building->code;
            $this->floor_count = $building->floor_count;
            $this->description = $building->description;
            $this->status = $building->status->value;
        } else {
            Gate::authorize('building.create');
            $this->property_id = Property::query()->value('id');
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'property_id' => ['required', 'exists:properties,id'],
            'name' => ['required', 'string', 'max:150'],
            'code' => [
                'nullable', 'string', 'max:20',
                Rule::unique('buildings', 'code')
                    ->where('property_id', $this->property_id)
                    ->ignore($this->building?->id),
            ],
            'floor_count' => ['nullable', 'integer', 'min:0', 'max:100'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(PropertyStatus::class)],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->building) {
            $this->building->update($data);
            session()->flash('status', 'Gedung berhasil diperbarui.');
        } else {
            Building::query()->create($data);
            session()->flash('status', 'Gedung berhasil ditambahkan.');
        }

        $this->redirectRoute('admin.buildings', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.building.form', [
            'statuses' => PropertyStatus::cases(),
            'properties' => Property::query()->orderBy('name')->pluck('name', 'id'),
        ])->layout('components.layouts.admin', [
            'title' => $this->building ? 'Ubah Gedung' : 'Tambah Gedung',
        ]);
    }
}
