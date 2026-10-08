<?php

namespace App\Policies;

use App\Models\MaintenanceTicket;
use App\Models\User;

class MaintenanceTicketPolicy
{
    public function view(User $user, MaintenanceTicket $ticket): bool
    {
        if ($user->hasAnyRole(['owner', 'admin', 'finance', 'technician'])) {
            return true;
        }

        return $ticket->tenant_id !== null && $ticket->tenant_id === $user->tenant?->id;
    }

    public function create(User $user): bool
    {
        return $user->can('maintenance.create');
    }

    public function update(User $user, MaintenanceTicket $ticket): bool
    {
        return $user->can('maintenance.update');
    }

    public function assign(User $user, MaintenanceTicket $ticket): bool
    {
        return $user->can('maintenance.assign');
    }

    public function close(User $user, MaintenanceTicket $ticket): bool
    {
        return $user->can('maintenance.close')
            || ($ticket->tenant_id !== null && $ticket->tenant_id === $user->tenant?->id);
    }
}
