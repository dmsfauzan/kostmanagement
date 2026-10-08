<?php

namespace App\Livewire\Admin\Maintenance;

use App\Enums\MaintenanceStatus;
use App\Models\MaintenanceTicket;
use App\Models\User;
use App\Services\MaintenanceService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

    public MaintenanceTicket $ticket;

    public string $status = '';

    public string $note = '';

    public string $comment = '';

    public bool $commentIsInternal = false;

    public ?int $assigned_to = null;

    /** @var array<int, mixed> */
    public array $photos = [];

    public function mount(MaintenanceTicket $ticket): void
    {
        Gate::authorize('maintenance.view');
        $this->ticket = $ticket;
        $this->status = $ticket->status->value;
        $this->assigned_to = $ticket->assigned_to;
    }

    public function assign(): void
    {
        Gate::authorize('maintenance.assign');

        $this->validate(['assigned_to' => ['required', 'exists:users,id']]);

        app(MaintenanceService::class)->assign($this->ticket, $this->assigned_to);
        session()->flash('status', 'Tiket ditugaskan.');
    }

    public function changeStatus(): void
    {
        Gate::authorize('maintenance.update');

        $this->validate([
            'status' => ['required', Rule::enum(MaintenanceStatus::class)],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            app(MaintenanceService::class)->transition(
                $this->ticket->refresh(),
                MaintenanceStatus::from($this->status),
                $this->note ?: null,
            );
            session()->flash('status', 'Status tiket diperbarui.');
        } catch (\InvalidArgumentException $thrown) {
            session()->flash('error', $thrown->getMessage());
        }
    }

    public function addComment(): void
    {
        Gate::authorize('maintenance.update');

        $this->validate(['comment' => ['required', 'string', 'max:1000']]);

        app(MaintenanceService::class)->addComment(
            $this->ticket,
            auth()->user(),
            $this->comment,
            $this->commentIsInternal,
        );

        $this->reset(['comment', 'commentIsInternal']);
        session()->flash('status', 'Komentar terkirim.');
    }

    public function addPhotos(): void
    {
        Gate::authorize('maintenance.update');

        $this->validate(['photos' => ['array', 'max:5'], 'photos.*' => ['image', 'max:4096']]);

        app(MaintenanceService::class)->storeAttachments($this->ticket, $this->photos);
        $this->reset('photos');
        session()->flash('status', 'Lampiran diunggah.');
    }

    public function render()
    {
        $ticket = $this->ticket->load(['tenant', 'room', 'assignee', 'creator', 'comments.user', 'attachments.uploader', 'statusHistories.actor']);

        return view('livewire.admin.maintenance.show', [
            'ticket' => $ticket,
            'statuses' => MaintenanceStatus::cases(),
            'technicians' => User::query()->role('technician')->pluck('name', 'id'),
        ])->layout('components.layouts.admin', ['title' => $ticket->ticket_number]);
    }
}
