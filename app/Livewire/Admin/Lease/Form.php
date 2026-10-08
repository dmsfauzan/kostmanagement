<?php

namespace App\Livewire\Admin\Lease;

use App\Enums\BillingCycle;
use App\Enums\LateFeeType;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Services\LeaseService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
    public ?Lease $lease = null;

    public ?int $tenant_id = null;

    public ?int $room_id = null;

    public ?int $property_id = null;

    public string $start_date = '';

    public string $end_date = '';

    public ?int $rent_amount = null;

    public ?int $deposit_amount = 0;

    public string $billing_cycle = 'monthly';

    public int $due_day = 1;

    public string $late_fee_type = 'none';

    public ?int $late_fee_value = 0;

    public ?string $notes = '';

    public function mount(?Lease $lease = null): void
    {
        if ($lease && $lease->exists) {
            Gate::authorize('lease.update');
            $this->lease = $lease;
            $this->tenant_id = $lease->tenant_id;
            $this->room_id = $lease->room_id;
            $this->property_id = $lease->property_id;
            $this->start_date = $lease->start_date->toDateString();
            $this->end_date = $lease->end_date->toDateString();
            $this->rent_amount = $lease->rent_amount;
            $this->deposit_amount = $lease->deposit_amount;
            $this->billing_cycle = $lease->billing_cycle->value;
            $this->due_day = $lease->due_day;
            $this->late_fee_type = $lease->late_fee_type->value;
            $this->late_fee_value = $lease->late_fee_value;
            $this->notes = $lease->notes;
        } else {
            Gate::authorize('lease.create');
            $this->property_id = Property::query()->value('id');
            $this->due_day = 1;
            $this->rent_amount = 1500000;
        }
    }

    public function updatedPropertyId(): void
    {
        $this->room_id = null;
        $this->tenant_id = null;
    }

    public function updatedRoomId(?int $roomId): void
    {
        if (! $roomId) {
            return;
        }

        $room = Room::query()->find($roomId);

        if ($room) {
            $this->property_id = $room->property_id;
            $this->rent_amount = $room->price;
            $this->deposit_amount = $room->deposit;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'tenant_id' => ['required', 'exists:tenants,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'property_id' => ['required', 'exists:properties,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'rent_amount' => ['required', 'integer', 'min:0'],
            'deposit_amount' => ['nullable', 'integer', 'min:0'],
            'billing_cycle' => ['required', Rule::enum(BillingCycle::class)],
            'due_day' => ['required', 'integer', 'min:1', 'max:28'],
            'late_fee_type' => ['required', Rule::enum(LateFeeType::class)],
            'late_fee_value' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        if (! $this->lease) {
            $existing = Lease::query()->where('tenant_id', $data['tenant_id'])
                ->where('room_id', $data['room_id'])
                ->whereIn('status', ['active', 'expiring'])
                ->where('start_date', '<=', $data['end_date'])
                ->where('end_date', '>=', $data['start_date'])
                ->exists();

            if ($existing) {
                $this->addError('start_date', 'Kamar atau penghuni sudah memiliki kontrak yang tumpang tindih.');

                return;
            }
        }

        $service = app(LeaseService::class);

        if ($this->lease) {
            $service->update($this->lease, $data);
            session()->flash('status', 'Kontrak berhasil diperbarui.');

            $this->redirectRoute('admin.leases.show', ['lease' => $this->lease->id], navigate: true);
        } else {
            $lease = $service->create($data);
            session()->flash('status', 'Kontrak berhasil dibuat.');

            $this->redirectRoute('admin.leases.show', ['lease' => $lease->id], navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.admin.lease.form', [
            'tenants' => Tenant::query()->orderBy('full_name')->get(),
            'properties' => Property::query()->orderBy('name')->pluck('name', 'id'),
            'rooms' => $this->property_id
                ? Room::query()->where('property_id', $this->property_id)->orderBy('number')->get()
                : collect(),
            'billingCycles' => BillingCycle::cases(),
            'lateFeeTypes' => LateFeeType::cases(),
        ])->layout('components.layouts.admin', [
            'title' => $this->lease ? 'Ubah Kontrak' : 'Tambah Kontrak',
        ]);
    }
}
