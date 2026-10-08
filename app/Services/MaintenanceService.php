<?php

namespace App\Services;

use App\Enums\MaintenanceStatus;
use App\Events\MaintenanceAssigned;
use App\Events\MaintenanceCreated;
use App\Events\MaintenanceStatusChanged;
use App\Models\MaintenanceTicket;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MaintenanceService
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly SettingsService $settings,
    ) {}

    /**
     * @param  array{title: string, category: string, description: string, priority: string}  $data
     * @param  list<UploadedFile>  $photos
     */
    public function create(Tenant $tenant, array $data, array $photos = []): MaintenanceTicket
    {
        $lease = $tenant->activeLease;
        $slaHours = (int) $this->settings->get('maintenance.sla_hours', 48);

        $ticket = DB::transaction(function () use ($tenant, $data, $lease, $slaHours): MaintenanceTicket {
            $ticket = MaintenanceTicket::query()->create([
                'property_id' => $tenant->property_id,
                'room_id' => $lease?->room_id,
                'tenant_id' => $tenant->id,
                'ticket_number' => $this->generateNumber(),
                'title' => $data['title'],
                'category' => $data['category'],
                'description' => $data['description'],
                'priority' => $data['priority'],
                'status' => MaintenanceStatus::Submitted,
                'sla_due_at' => now()->addHours($slaHours),
                'created_by' => auth()->id(),
            ]);

            $this->writeHistory($ticket, null, MaintenanceStatus::Submitted, 'Tiket dibuat');

            return $ticket;
        });

        $this->storeAttachments($ticket, $photos);

        $this->audit->record('maintenance.created', $ticket, [], $ticket->only([
            'ticket_number', 'title', 'priority', 'status',
        ]), 'Maintenance');

        event(new MaintenanceCreated($ticket));

        return $ticket->refresh();
    }

    public function assign(MaintenanceTicket $ticket, int $userId): MaintenanceTicket
    {
        $before = $ticket->only(['assigned_to']);
        $ticket->update(['assigned_to' => $userId]);

        $this->audit->record('maintenance.assigned', $ticket, $before, [
            'assigned_to' => $userId,
        ], 'Maintenance');

        event(new MaintenanceAssigned($ticket->load('assignee')));

        return $ticket->refresh();
    }

    public function transition(MaintenanceTicket $ticket, MaintenanceStatus $to, ?string $note = null): MaintenanceTicket
    {
        $from = $ticket->status;

        if ($from === $to) {
            throw new InvalidArgumentException('Status sudah sesuai.');
        }

        if (! $from->canTransitionTo($to)) {
            throw new InvalidArgumentException("Transisi dari {$from->label()} ke {$to->label()} tidak diizinkan.");
        }

        DB::transaction(function () use ($ticket, $from, $to, $note): void {
            $ticket->update([
                'status' => $to,
                'acknowledged_at' => $to === MaintenanceStatus::Acknowledged ? now() : $ticket->acknowledged_at,
                'resolved_at' => $to === MaintenanceStatus::Resolved ? now() : $ticket->resolved_at,
                'closed_at' => $to === MaintenanceStatus::Closed ? now() : $ticket->closed_at,
            ]);

            $this->writeHistory($ticket, $from, $to, $note);
        });

        $this->audit->record('maintenance.status_changed', $ticket, [
            'status' => $from->value,
        ], [
            'status' => $to->value,
            'note' => $note,
        ], 'Maintenance');

        event(new MaintenanceStatusChanged($ticket->refresh(), $from->value, $to->value));

        return $ticket->refresh();
    }

    public function addComment(MaintenanceTicket $ticket, User $user, string $body, bool $isInternal = false): void
    {
        $ticket->comments()->create([
            'user_id' => $user->id,
            'body' => $body,
            'is_internal' => $isInternal,
        ]);

        $this->audit->record('maintenance.commented', $ticket, [], [
            'internal' => $isInternal,
        ], 'Maintenance');
    }

    /**
     * @param  list<UploadedFile>  $files
     */
    public function storeAttachments(MaintenanceTicket $ticket, array $files): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store("maintenance/{$ticket->id}", config('kost.disk.private'));

            $ticket->attachments()->create([
                'path' => $path,
                'type' => $file->getClientOriginalExtension(),
                'uploaded_by' => auth()->id(),
            ]);
        }
    }

    private function writeHistory(MaintenanceTicket $ticket, ?MaintenanceStatus $from, MaintenanceStatus $to, ?string $note = null): void
    {
        $ticket->statusHistories()->create([
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'note' => $note,
            'actor_id' => auth()->id(),
        ]);
    }

    private function generateNumber(): string
    {
        $year = now()->year;
        $sequence = MaintenanceTicket::query()->whereYear('created_at', $year)->count() + 1;

        do {
            $number = sprintf('MNT-%d-%04d', $year, $sequence);
            $sequence++;
        } while (MaintenanceTicket::query()->where('ticket_number', $number)->exists());

        return $number;
    }
}
