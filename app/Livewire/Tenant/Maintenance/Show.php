<?php

namespace App\Livewire\Tenant\Maintenance;

use App\Enums\MaintenanceStatus;
use App\Models\MaintenanceTicket;
use App\Services\MaintenanceService;
use Illuminate\View\View;
use Livewire\Component;

class Show extends Component
{
    public MaintenanceTicket $ticket;

    public string $comment = '';

    public function mount(MaintenanceTicket $ticket): void
    {
        abort_unless($ticket->tenant_id === auth()->user()->tenant?->id, 404);

        $this->ticket = $ticket;
    }

    public function addComment(): void
    {
        $this->validate(['comment' => ['required', 'string', 'max:1000']]);

        app(MaintenanceService::class)->addComment($this->ticket, auth()->user(), $this->comment, false);

        $this->reset('comment');
        session()->flash('status', 'Komentar terkirim.');
    }

    public function confirmResolved(): void
    {
        $this->transition(MaintenanceStatus::Closed, 'Dikonfirmasi selesai oleh penghuni');
    }

    public function reopen(): void
    {
        $this->transition(MaintenanceStatus::InProgress, 'Dibuka kembali oleh penghuni');
    }

    private function transition(MaintenanceStatus $status, string $note): void
    {
        try {
            app(MaintenanceService::class)->transition($this->ticket->refresh(), $status, $note);
            session()->flash('status', 'Status keluhan diperbarui.');
        } catch (\InvalidArgumentException $thrown) {
            session()->flash('error', $thrown->getMessage());
        }
    }

    public function render(): View
    {
        $ticket = $this->ticket->load(['room', 'assignee', 'statusHistories.actor']);

        return view('livewire.tenant.maintenance.show', [
            'ticket' => $ticket,
            'comments' => $ticket->comments()->where('is_internal', false)->with('user')->get(),
            'attachments' => $ticket->attachments,
        ])->layout('components.layouts.tenant', ['title' => 'Keluhan '.$ticket->ticket_number]);
    }
}
