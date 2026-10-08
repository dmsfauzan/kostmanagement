<?php

namespace App\Listeners;

use App\Events\MaintenanceCreated;
use App\Models\User;
use App\Notifications\MaintenanceCreatedNotification;

class NotifyStaffOfMaintenanceCreated
{
    public function handle(MaintenanceCreated $event): void
    {
        User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['owner', 'admin', 'technician']))
            ->get()
            ->each(fn (User $user) => $user->notify(new MaintenanceCreatedNotification($event->ticket)));
    }
}
