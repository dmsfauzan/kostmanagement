<?php

namespace App\Livewire\Admin\Room;

use App\Enums\RoomStatus;
use App\Models\Amenity;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\RoomService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public ?Room $room = null;

    public ?int $property_id = null;

    public ?int $building_id = null;

    public ?int $floor_id = null;

    public ?int $room_type_id = null;

    public string $number = '';

    public ?int $price = null;

    public ?int $deposit = 0;

    public ?int $size_sqm = null;

    public ?string $description = '';

    public string $status = 'available';

    /** @var list<int> */
    public array $selectedAmenities = [];

    /** @var array<int, mixed> */
    public array $photos = [];

    public function mount(?Room $room = null): void
    {
        if ($room && $room->exists) {
            Gate::authorize('room.update');
            $this->room = $room->load('photos');
            $this->property_id = $room->property_id;
            $this->building_id = $room->building_id;
            $this->floor_id = $room->floor_id;
            $this->room_type_id = $room->room_type_id;
            $this->number = $room->number;
            $this->price = $room->price;
            $this->deposit = $room->deposit;
            $this->size_sqm = $room->size_sqm;
            $this->description = $room->description;
            $this->status = $room->status->value;
            $this->selectedAmenities = $room->amenities()->pluck('amenities.id')->all();
        } else {
            Gate::authorize('room.create');
            $this->property_id = Property::query()->value('id');
            $this->price = 1500000;
        }
    }

    public function updatedPropertyId(): void
    {
        $this->building_id = null;
        $this->floor_id = null;
        $this->room_type_id = null;
    }

    public function updatedBuildingId(): void
    {
        $this->floor_id = null;
    }

    public function updatedRoomTypeId(?int $roomTypeId): void
    {
        if (! $roomTypeId) {
            return;
        }

        $type = RoomType::query()->find($roomTypeId);

        if ($type) {
            $this->price = $type->default_price;
            $this->deposit = $type->default_deposit;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'property_id' => ['required', 'exists:properties,id'],
            'building_id' => ['required', 'exists:buildings,id'],
            'floor_id' => ['required', Rule::exists('floors', 'id')->where('building_id', $this->building_id)],
            'room_type_id' => ['nullable', 'exists:room_types,id'],
            'number' => [
                'required', 'string', 'max:30',
                Rule::unique('rooms', 'number')
                    ->where('property_id', $this->property_id)
                    ->whereNull('deleted_at')
                    ->ignore($this->room?->id),
            ],
            'price' => ['required', 'integer', 'min:0'],
            'deposit' => ['nullable', 'integer', 'min:0'],
            'size_sqm' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(RoomStatus::class)],
            'selectedAmenities' => ['array'],
            'selectedAmenities.*' => ['exists:amenities,id'],
            'photos' => ['array'],
            'photos.*' => ['image', 'max:4096'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        $photos = $this->photos;
        $amenityIds = $data['selectedAmenities'] ?? [];
        unset($data['selectedAmenities'], $data['photos']);

        $data['deposit'] = $data['deposit'] ?? 0;

        $service = app(RoomService::class);

        if ($this->room) {
            $room = $service->update($this->room, $data, $amenityIds, $photos);
            session()->flash('status', 'Kamar '.$room->number.' berhasil diperbarui.');
        } else {
            $room = $service->create($data, $amenityIds, $photos);
            session()->flash('status', 'Kamar '.$room->number.' berhasil ditambahkan.');
        }

        $this->redirectRoute('admin.rooms', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.room.form', [
            'statuses' => RoomStatus::cases(),
            'properties' => Property::query()->orderBy('name')->pluck('name', 'id'),
            'buildings' => $this->property_id
                ? Building::query()->where('property_id', $this->property_id)->orderBy('name')->pluck('name', 'id')
                : collect(),
            'floors' => $this->building_id
                ? Floor::query()->where('building_id', $this->building_id)->orderBy('level')->get()
                : collect(),
            'roomTypes' => $this->property_id
                ? RoomType::query()->where('property_id', $this->property_id)->orderBy('name')->pluck('name', 'id')
                : collect(),
            'amenities' => Amenity::query()->where('is_active', true)->orderBy('name')->get(),
        ])->layout('components.layouts.admin', [
            'title' => $this->room ? 'Ubah Kamar' : 'Tambah Kamar',
        ]);
    }
}
