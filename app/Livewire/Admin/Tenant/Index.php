<?php

namespace App\Livewire\Admin\Tenant;

use App\Enums\TenantStatus;
use App\Models\Property;
use App\Models\Tenant;
use App\Services\TenantService;
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

    #[Url(except: '')]
    public string $property = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingProperty(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        Gate::authorize('tenant.update');

        $tenant = Tenant::query()->findOrFail($id);

        if ($tenant->leases()->open()->exists()) {
            session()->flash('error', 'Penghuni tidak dapat dihapus karena memiliki kontrak aktif.');

            return;
        }

        app(TenantService::class)->destroy($tenant);
        session()->flash('status', 'Penghuni berhasil dihapus.');
    }

    public function resendInvite(int $id): void
    {
        Gate::authorize('tenant.update');

        $tenant = Tenant::query()->with('user')->findOrFail($id);

        if (blank($tenant->email)) {
            session()->flash('error', 'Penghuni tidak memiliki email.');

            return;
        }

        app(TenantService::class)->invite($tenant);
        session()->flash('status', 'Undangan aktivasi telah dikirim ulang ke '.$tenant->email.'.');
    }

    public function render()
    {
        $tenants = Tenant::query()
            ->with(['property', 'user'])
            ->when($this->search, fn ($query) => $query->where(
                fn ($q) => $q->where('full_name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('nik', 'like', "%{$this->search}%")
            ))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->property, fn ($query) => $query->where('property_id', $this->property))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.tenant.index', [
            'tenants' => $tenants,
            'statuses' => TenantStatus::cases(),
            'properties' => Property::query()->orderBy('name')->pluck('name', 'id'),
        ])->layout('components.layouts.admin', ['title' => 'Penghuni']);
    }
}
