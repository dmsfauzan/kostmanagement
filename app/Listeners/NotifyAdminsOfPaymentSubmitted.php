<?php

namespace App\Listeners;

use App\Events\PaymentSubmitted;
use App\Models\User;
use App\Notifications\PaymentSubmittedNotification;

class NotifyAdminsOfPaymentSubmitted
{
    public function handle(PaymentSubmitted $event): void
    {
        User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['owner', 'admin', 'finance']))
            ->get()
            ->each(fn (User $user) => $user->notify(new PaymentSubmittedNotification($event->payment)));
    }
}
