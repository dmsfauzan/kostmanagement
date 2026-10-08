<?php

namespace App\Listeners;

use App\Events\MaintenanceAssigned;
use App\Notifications\MaintenanceAssignedNotification;

class SendMaintenanceAssignedNotification
{
    public function handle(MaintenanceAssigned $event): void
    {
        $assignee = $event->ticket->assignee;

        if ($assignee) {
            $assignee->notify(new MaintenanceAssignedNotification($event->ticket));
        }
    }
}
