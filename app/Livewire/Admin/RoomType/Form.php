<?php

namespace App\Livewire\Admin\RoomType;

use App\Models\Amenity;
use App\Models\Property;
use App\Models\RoomType;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Form extends Component
{
    public ?RoomType $roomType = null;

    public ?int $property_id = null;

    public string $name = '';

    public ?int $default_price = null;

    public ?int $default_deposit = null;

    public ?int $size_sqm = null;

    public int $capacity = 1;

    public ?string $description = '';

    /** @var list<int> */
    public array $selectedAmenities = [];

    public function mount(?RoomType $roomType = null): void
    {
        if ($roomType && $roomType->exists) {
            Gate::authorize('room_type.update');
            $this->roomType = $roomType;
            $this->property_id = $roomType->property_id;
            $this->name = $roomType->name;
            $this->default_price = $roomType->default_price;
            $this->default_deposit = $roomType->default_deposit;
            $this->size_sqm = $roomType->size_sqm;
            $this->capacity = $roomType->capacity;
            $this->description = $roomType->description;
            $this->selectedAmenities = $roomType->amenities()->pluck('amenities.id')->all();
        } else {
            Gate::authorize('room_type.create');
            $this->property_id = Property::query()->value('id');
            $this->default_price = 1500000;
            $this->default_deposit = 500000;
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
            'default_price' => ['nullable', 'integer', 'min:0'],
            'default_deposit' => ['nullable', 'integer', 'min:0'],
            'size_sqm' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'capacity' => ['required', 'integer', 'min:1', 'max:10'],
            'description' => ['nullable', 'string'],
            'selectedAmenities' => ['array'],
            'selectedAmenities.*' => ['exists:amenities,id'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        $amenityIds = $data['selectedAmenities'] ?? [];
        unset($data['selectedAmenities']);

        if ($this->roomType) {
            $this->roomType->update($data);
            $this->roomType->amenities()->sync($amenityIds);
            session()->flash('status', 'Tipe kamar berhasil diperbarui.');
        } else {
            $type = RoomType::query()->create($data);
            $type->amenities()->sync($amenityIds);
            session()->flash('status', 'Tipe kamar berhasil ditambahkan.');
        }

        $this->redirectRoute('admin.room-types', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.room-type.form', [
            'properties' => Property::query()->orderBy('name')->pluck('name', 'id'),
            'amenities' => Amenity::query()->where('is_active', true)->orderBy('name')->get(),
        ])->layout('components.layouts.admin', [
            'title' => $this->roomType ? 'Ubah Tipe Kamar' : 'Tambah Tipe Kamar',
        ]);
    }
}
