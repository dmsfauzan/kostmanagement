<?php

namespace App\Listeners;

use App\Events\MaintenanceSlaBreached;
use App\Models\User;
use App\Notifications\MaintenanceSlaNotification;

class NotifyStaffOfMaintenanceSla
{
    public function handle(MaintenanceSlaBreached $event): void
    {
        User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['owner', 'admin']))
            ->get()
            ->each(fn (User $user) => $user->notify(new MaintenanceSlaNotification($event->ticket)));
    }
}
