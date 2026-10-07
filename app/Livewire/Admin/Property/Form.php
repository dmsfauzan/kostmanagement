<?php

namespace App\Livewire\Admin\Property;

use App\Enums\PropertyStatus;
use App\Models\Property;
use App\Services\AuditService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public ?Property $property = null;

    public string $name = '';

    public ?string $address = '';

    public ?string $city = '';

    public ?string $province = '';

    public ?string $postal_code = '';

    public ?string $phone = '';

    public ?string $email = '';

    public ?string $description = '';

    public string $status = 'active';

    public $logo;

    public $cover;

    public ?string $existingLogo = null;

    public ?string $existingCover = null;

    public function mount(?Property $property = null): void
    {
        if ($property && $property->exists) {
            Gate::authorize('property.update');

            $this->property = $property;
            $this->name = $property->name;
            $this->address = $property->address;
            $this->city = $property->city;
            $this->province = $property->province;
            $this->postal_code = $property->postal_code;
            $this->phone = $property->phone;
            $this->email = $property->email;
            $this->description = $property->description;
            $this->status = $property->status->value;
            $this->existingLogo = $property->logo;
            $this->existingCover = $property->cover;
        } else {
            Gate::authorize('property.create');
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(PropertyStatus::class)],
            'logo' => ['nullable', 'image', 'max:4096'],
            'cover' => ['nullable', 'image', 'max:4096'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        unset($data['logo'], $data['cover']);

        $disk = config('kost.disk.public');

        if ($this->logo) {
            $data['logo'] = $this->logo->store('properties', $disk);
        }

        if ($this->cover) {
            $data['cover'] = $this->cover->store('properties', $disk);
        }

        $audit = app(AuditService::class);

        if ($this->property) {
            $before = $this->property->only(['name', 'city', 'status']);
            $this->property->update($data);
            $audit->record('property.updated', $this->property, $before, $this->property->only(['name', 'city', 'status']), 'Property');
            session()->flash('status', 'Properti berhasil diperbarui.');
        } else {
            $data['owner_id'] = auth()->id();
            $property = Property::query()->create($data);
            $audit->record('property.created', $property, [], $property->only(['name', 'city']), 'Property');
            session()->flash('status', 'Properti berhasil ditambahkan.');
        }

        $this->redirectRoute('admin.properties', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.property.form', [
            'statuses' => PropertyStatus::cases(),
        ])->layout('components.layouts.admin', [
            'title' => $this->property ? 'Ubah Properti' : 'Tambah Properti',
        ]);
    }
}
