<?php

namespace App\Livewire\Admin\Lease;

use App\Enums\LeaseStatus;
use App\Models\Lease;
use App\Models\Tenant;
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

    #[Url(except: '')]
    public string $tenant = '';

    public function updating(string $field): void
    {
        if (in_array($field, ['search', 'status', 'tenant'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $leases = Lease::query()
            ->with(['tenant', 'room', 'property'])
            ->when($this->search, fn ($query) => $query->where(
                fn ($q) => $q->where('code', 'like', "%{$this->search}%")
                    ->orWhereHas('tenant', fn ($t) => $t->where('full_name', 'like', "%{$this->search}%"))
            ))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->tenant, fn ($query) => $query->where('tenant_id', $this->tenant))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.lease.index', [
            'leases' => $leases,
            'statuses' => LeaseStatus::cases(),
            'tenants' => Tenant::query()->orderBy('full_name')->pluck('full_name', 'id'),
        ])->layout('components.layouts.admin', ['title' => 'Kontrak Sewa']);
    }
}
