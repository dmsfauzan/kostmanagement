<?php

namespace App\Livewire\Admin\Lease;

use App\Models\Lease;
use App\Services\LeaseService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Show extends Component
{
    public Lease $lease;

    public function mount(Lease $lease): void
    {
        Gate::authorize('lease.view');
        $this->lease = $lease;
    }

    public function activate(): void
    {
        Gate::authorize('lease.update');

        try {
            app(LeaseService::class)->activate($this->lease->refresh());
            session()->flash('status', 'Kontrak berhasil diaktifkan. Kamar menjadi terisi.');
        } catch (\InvalidArgumentException $thrown) {
            session()->flash('error', $thrown->getMessage());
        }
    }

    public function render()
    {
        $lease = $this->lease->load([
            'tenant',
            'room.property',
            'room.floor',
            'deposit',
            'statusHistories.actor',
        ]);

        return view('livewire.admin.lease.show', [
            'lease' => $lease,
        ])->layout('components.layouts.admin', ['title' => 'Kontrak '.$lease->code]);
    }
}
