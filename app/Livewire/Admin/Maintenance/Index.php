<?php

namespace App\Livewire\Admin\Maintenance;

use App\Enums\MaintenanceCategory;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Models\MaintenanceTicket;
use App\Models\User;
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
    public string $priority = '';

    #[Url(except: '')]
    public string $category = '';

    public function updating(string $field): void
    {
        if (in_array($field, ['search', 'status', 'priority', 'category'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        Gate::authorize('maintenance.view');

        $tickets = MaintenanceTicket::query()
            ->with(['tenant', 'room', 'assignee'])
            ->when($this->search, fn ($query) => $query->where(
                fn ($q) => $q->where('ticket_number', 'like', "%{$this->search}%")
                    ->orWhere('title', 'like', "%{$this->search}%")
            ))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->priority, fn ($query) => $query->where('priority', $this->priority))
            ->when($this->category, fn ($query) => $query->where('category', $this->category))
            ->latest()
            ->paginate(12);

        return view('livewire.admin.maintenance.index', [
            'tickets' => $tickets,
            'statuses' => MaintenanceStatus::cases(),
            'priorities' => MaintenancePriority::cases(),
            'categories' => MaintenanceCategory::cases(),
            'technicians' => User::query()->role('technician')->pluck('name', 'id'),
        ])->layout('components.layouts.admin', ['title' => 'Maintenance']);
    }
}
