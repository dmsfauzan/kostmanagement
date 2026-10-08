<?php

namespace App\Listeners;

use App\Events\PaymentRejected;
use App\Notifications\PaymentRejectedNotification;

class SendPaymentRejectedNotification
{
    public function handle(PaymentRejected $event): void
    {
        $user = $event->payment->tenant?->user;

        if ($user) {
            $user->notify(new PaymentRejectedNotification($event->payment));
        }
    }
}
