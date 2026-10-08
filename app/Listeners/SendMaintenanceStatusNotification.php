<?php

namespace App\Listeners;

use App\Events\MaintenanceStatusChanged;
use App\Notifications\MaintenanceStatusNotification;

class SendMaintenanceStatusNotification
{
    public function handle(MaintenanceStatusChanged $event): void
    {
        $user = $event->ticket->tenant?->user;

        if ($user) {
            $user->notify(new MaintenanceStatusNotification($event->ticket, $event->fromStatus, $event->toStatus));
        }
    }
}
