<?php

namespace App\Livewire\Admin\Tenant;

use App\Enums\Gender;
use App\Models\Property;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
    public ?Tenant $tenant = null;

    public ?int $property_id = null;

    public string $full_name = '';

    public ?string $nik = '';

    public string $gender = '';

    public ?string $birth_date = '';

    public ?string $phone = '';

    public ?string $email = '';

    public ?string $address = '';

    public ?string $emergency_name = '';

    public ?string $emergency_relationship = '';

    public ?string $emergency_phone = '';

    public ?string $vehicle_type = '';

    public ?string $vehicle_number = '';

    public ?string $notes = '';

    public bool $skip_invite = false;

    public function mount(?Tenant $tenant = null): void
    {
        if ($tenant && $tenant->exists) {
            Gate::authorize('tenant.update');

            $this->tenant = $tenant;
            $this->property_id = $tenant->property_id;
            $this->full_name = $tenant->full_name;
            $this->nik = $tenant->nik;
            $this->gender = $tenant->gender?->value ?? '';
            $this->birth_date = $tenant->birth_date?->toDateString();
            $this->phone = $tenant->phone;
            $this->email = $tenant->email;
            $this->address = $tenant->address;
            $this->emergency_name = $tenant->emergency_name;
            $this->emergency_relationship = $tenant->emergency_relationship;
            $this->emergency_phone = $tenant->emergency_phone;
            $this->vehicle_type = $tenant->vehicle_type;
            $this->vehicle_number = $tenant->vehicle_number;
            $this->notes = $tenant->notes;
        } else {
            Gate::authorize('tenant.create');
            $this->property_id = Property::query()->value('id');
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'property_id' => ['nullable', 'exists:properties,id'],
            'full_name' => ['required', 'string', 'max:150'],
            'nik' => ['nullable', 'string', 'max:32', Rule::unique('tenants', 'nik')->ignore($this->tenant?->id)],
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => [
                Rule::requiredIf(fn () => ! $this->tenant && ! $this->skip_invite),
                'nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($this->tenant?->user_id),
            ],
            'address' => ['nullable', 'string'],
            'emergency_name' => ['nullable', 'string', 'max:150'],
            'emergency_relationship' => ['nullable', 'string', 'max:50'],
            'emergency_phone' => ['nullable', 'string', 'max:30'],
            'vehicle_type' => ['nullable', 'string', 'max:30'],
            'vehicle_number' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['skip_invite'] = $this->skip_invite || $this->tenant !== null;

        $service = app(TenantService::class);

        if ($this->tenant) {
            $tenant = $service->update($this->tenant, $data);
            session()->flash('status', 'Data penghuni berhasil diperbarui.');

            $this->redirectRoute('admin.tenants.show', ['tenant' => $tenant->id], navigate: true);
        } else {
            $tenant = $service->create($data);

            if ($tenant->hasAccount()) {
                session()->flash('status', 'Penghuni berhasil ditambahkan dan undangan aktivasi telah dikirim.');
            } else {
                session()->flash('status', 'Penghuni berhasil ditambahkan.');
            }

            $this->redirectRoute('admin.tenants.show', ['tenant' => $tenant->id], navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.admin.tenant.form', [
            'properties' => Property::query()->orderBy('name')->pluck('name', 'id'),
            'genders' => Gender::cases(),
        ])->layout('components.layouts.admin', [
            'title' => $this->tenant ? 'Ubah Penghuni' : 'Tambah Penghuni',
        ]);
    }
}
