<?php

namespace App\Livewire\Tenant\Maintenance;

use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function render(): View
    {
        $tenant = auth()->user()->tenant;

        abort_unless($tenant, 404);

        $tickets = $tenant->tickets()
            ->with('room')
            ->latest()
            ->paginate(10);

        return view('livewire.tenant.maintenance.index', [
            'tickets' => $tickets,
        ])->layout('components.layouts.tenant', ['title' => 'Keluhan']);
    }
}
